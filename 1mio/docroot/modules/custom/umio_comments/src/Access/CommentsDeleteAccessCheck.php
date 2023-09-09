<?php

namespace Drupal\umio_comments\Access;

use Drupal\comment\Entity\Comment;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Check if the user can delete the comment.
 */
class CommentsDeleteAccessCheck implements AccessInterface {

  /**
   * The current route.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $currentRouteMatch;

  /**
   * The construct method.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $currentRouteMatch
   *   The current route.
   */
  final public function __construct(CurrentRouteMatch $currentRouteMatch) {
    $this->currentRouteMatch = $currentRouteMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('current_route_match'),
    );
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account) {
    /** @var \Drupal\comment\Entity\Comment|null $comment */
    $comment = $this->currentRouteMatch->getParameter('comment');
    if (!$comment) {
      return AccessResult::neutral();
    }
    if (!$comment instanceof Comment) {
      $comment = Comment::load($comment);
      if (!$comment) {
        return AccessResult::forbidden();
      }
    }

    $user = User::load($account->id());
    if ($account->isAuthenticated() && $user) {
      $roles = $account->getRoles();
      if (in_array('administrator', $roles) ||
          in_array('company_manager', $roles)
      ) {
        return AccessResult::allowed();
      }

      $nodeId = $comment->getCommentedEntityId();
      $node = Node::load($nodeId);
      // If node owner.
      if ($node && $node->getOwnerId() === $user->id()) {
        return AccessResult::allowed();
      }

      // If comment owner.
      if ($comment->getOwnerId() === $user->id()) {
        return AccessResult::allowed();
      }
    }

    // If not authenticated throw don't allow.
    // Or if not enter in any condition.
    return AccessResult::forbidden();
  }

}
