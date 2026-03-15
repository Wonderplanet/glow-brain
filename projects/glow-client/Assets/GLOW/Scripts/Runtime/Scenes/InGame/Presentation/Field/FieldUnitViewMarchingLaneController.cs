using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Presentation.Constants;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class FieldUnitViewMarchingLaneController
    {
        static readonly Vector3 OriginPosition = new Vector3(0, 0.8f, 0);
        static readonly Vector3 PositionInterval = new Vector3(0, -0.006f, -10f);

        FieldUnitView _specialAttackUnitView;   // 必殺ワザ中のキャラ

        public void ApplyMarchingPosition(FieldUnitView unitView)
        {
            var position = GetMarchingPosition(unitView.MarchingLane);
            unitView.SetMarchingLanePos(position);
        }

        public void ChangeToSpecialAttackPosition(FieldUnitView unitView)
        {
            // 既に必殺ワザ中のキャラがいる場合は、その位置を元に戻す
            if (_specialAttackUnitView != null)
            {
                ApplyMarchingPosition(_specialAttackUnitView);
            }

            _specialAttackUnitView = unitView;

            // 必殺ワザ中はキャラを手前に表示する
            var position = GetMarchingPosition(unitView.MarchingLane);
            position.z = FieldZPositionDefinitions.SpecialAttack - FieldZPositionDefinitions.UnitRoot;

            unitView.SetMarchingLanePos(position);
        }

        public void ReturnToMarchingPositionFromSpecialAttackPosition(FieldUnitView unitView)
        {
            if (_specialAttackUnitView != unitView) return;

            ApplyMarchingPosition(_specialAttackUnitView);
            _specialAttackUnitView = null;
        }

        public void ChangeToEscapePosition(FieldUnitView unitView)
        {
            var position = GetMarchingPosition(unitView.MarchingLane);
            position.z = FieldZPositionDefinitions.UnitEscape - FieldZPositionDefinitions.UnitRoot;

            unitView.SetMarchingLanePos(position);
        }

        Vector3 GetMarchingPosition(MarchingLaneIdentifier laneIdentifier)
        {
            if (laneIdentifier.IsEmpty()) return OriginPosition;

            var laneIndex = laneIdentifier.LaneIndex.Value;
            var laneIndexOffset = laneIdentifier.LaneIndexOffset.Value;
            var position = OriginPosition + PositionInterval * laneIndex + PositionInterval * laneIndexOffset;

            return position;
        }
    }
}
