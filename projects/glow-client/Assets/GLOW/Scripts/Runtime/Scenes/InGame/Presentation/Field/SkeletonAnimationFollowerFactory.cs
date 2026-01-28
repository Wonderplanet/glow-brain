using GLOW.Modules.Spine.Presentation;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public static class SkeletonAnimationFollowerFactory
    {
        public static void BindSkeletonAnimation(GameObject gameObject, SkeletonAnimation skeletonAnimation, string attachTargetBone, bool followBoneRotation)
        {
            var boneFollower = gameObject.AddComponent<SpineBoneFollower>();
            boneFollower.SkeletonRenderer = skeletonAnimation;
            boneFollower.BoneName = attachTargetBone;

            boneFollower.FollowXYPosition = true;
            boneFollower.FollowZPosition = false;
            boneFollower.FollowBoneRotation = followBoneRotation;
            boneFollower.FollowLocalScale = false;
            boneFollower.FollowParentWorldScale = false;
            boneFollower.FollowSkeletonFlip = false;

            boneFollower.Initialize();
            boneFollower.LateUpdate();
        }

    }
}
