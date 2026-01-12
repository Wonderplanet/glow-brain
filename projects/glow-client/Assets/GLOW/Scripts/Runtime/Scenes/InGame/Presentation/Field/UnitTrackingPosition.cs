using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Spine;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class UnitTrackingPosition : IFieldViewPositionTrackerTarget
    {
        UnitImage _unitImage;
        Bone _trackingBone;

        public UnitTrackingPosition(UnitImage unitImage)
        {
            _unitImage = unitImage;

            _trackingBone = _unitImage.SpineBone ?? _unitImage.RootBone;
        }

        Vector2 IFieldViewPositionTrackerTarget.GetWorldPos()
        {
            return _trackingBone.GetWorldPosition(_unitImage.SkeletonAnimation.transform);
        }

        bool IFieldViewPositionTrackerTarget.IsDestroyed()
        {
            return _unitImage == null;
        }
    }
}
