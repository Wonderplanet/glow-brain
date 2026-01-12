using GLOW.Scenes.InGame.Presentation.Common;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class UnitTagPosition : IFieldViewPositionTrackerTarget
    {
        UnitImage _unitImage;

        public UnitTagPosition(UnitImage unitImage)
        {
            _unitImage = unitImage;
        }

        Vector2 IFieldViewPositionTrackerTarget.GetWorldPos()
        {
            return _unitImage.TagPosition.position;
        }

        bool IFieldViewPositionTrackerTarget.IsDestroyed()
        {
            return _unitImage == null;
        }
    }
}
