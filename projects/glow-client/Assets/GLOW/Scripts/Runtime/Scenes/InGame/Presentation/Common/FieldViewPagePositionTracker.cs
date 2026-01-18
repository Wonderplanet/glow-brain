using GLOW.Scenes.InGame.Domain.Battle;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public class FieldViewPagePositionTracker
    {
        readonly IFieldViewPagePositionTrackerTarget _target;
        readonly IViewCoordinateConverter _viewCoordinateConverter;
        readonly ICoordinateConverter _coordinateConverter;
        readonly float _pageWidth;

        public FieldViewPagePositionTracker(
            IFieldViewPagePositionTrackerTarget target,
            IViewCoordinateConverter viewCoordinateConverter,
            ICoordinateConverter coordinateConverter,
            float pageWidth)
        {
            _target = target;
            _viewCoordinateConverter = viewCoordinateConverter;
            _coordinateConverter = coordinateConverter;
            _pageWidth = pageWidth;
        }

        public Vector2 GetPageUIPos()
        {
            if (_target.IsDestroyed()) return Vector2.zero;

            var pos = _target.GetFieldViewCoordPos();

            var fieldCoordPos = _viewCoordinateConverter.ToFieldCoord(pos);
            var pageCoordPos = _coordinateConverter.FieldToPageCoord(fieldCoordPos);

            var effectPos = new Vector2(pageCoordPos.X * _pageWidth, pageCoordPos.Y * _pageWidth);
            return effectPos * -1f;
        }

        public bool IsTargetDestroyed()
        {
            return _target.IsDestroyed();
        }
    }
}
