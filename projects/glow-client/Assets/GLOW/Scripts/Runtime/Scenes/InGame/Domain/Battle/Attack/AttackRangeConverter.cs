using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public static class AttackRangeConverter
    {
        public static CoordinateRange ToFieldCoordAttackRange(
            BattleSide battleSide,
            OutpostCoordV2 characterUnitPos,
            AttackRange attackRange,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            if (attackRange.IsEmpty()) return CoordinateRange.Empty;

            var startFieldCoord = GetAttackRangePoint(
                battleSide,
                characterUnitPos,
                attackRange.StartPointType,
                attackRange.StartPointParameter,
                true,
                mstPageModel,
                coordinateConverter);

            var endFieldCoord = GetAttackRangePoint(
                battleSide,
                characterUnitPos,
                attackRange.EndPointType,
                attackRange.EndPointParameter,
                false,
                mstPageModel,
                coordinateConverter);

            if (startFieldCoord.IsEmpty() || endFieldCoord.IsEmpty())
            {
                return CoordinateRange.Empty;
            }

            // ReSharper disable once CompareOfFloatsByEqualityOperator
            if (startFieldCoord.X == endFieldCoord.X)
            {
                return CoordinateRange.Empty;
            }

            return CoordinateRange.BetweenPoints(startFieldCoord.X, endFieldCoord.X);
        }

        static FieldCoordV2 GetAttackRangePoint(
            BattleSide battleSide,
            OutpostCoordV2 characterUnitPos,
            AttackRangePointType pointType,
            AttackRangeParameter rangeParameter,
            bool isStartPoint,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            switch (pointType)
            {
                case AttackRangePointType.Distance:
                    return GetAttackRangePointByDistance(
                        battleSide,
                        characterUnitPos,
                        rangeParameter,
                        mstPageModel,
                        coordinateConverter);

                case AttackRangePointType.Koma:
                    return GetAttackRangePointByKoma(
                        battleSide,
                        characterUnitPos,
                        rangeParameter,
                        isStartPoint,
                        mstPageModel,
                        coordinateConverter);

                case AttackRangePointType.KomaLine:
                    return GetAttackRangePointByKomaLine(
                        battleSide,
                        characterUnitPos,
                        rangeParameter,
                        isStartPoint,
                        mstPageModel,
                        coordinateConverter);
                case AttackRangePointType.Page:
                    return GetAttackRangePointByPage(
                        battleSide,
                        characterUnitPos,
                        isStartPoint,
                        mstPageModel,
                        coordinateConverter);
                default:
                    return FieldCoordV2.Empty;
            }
        }

        static FieldCoordV2 GetAttackRangePointByDistance(
            BattleSide battleSide,
            OutpostCoordV2 characterUnitPos,
            AttackRangeParameter rangeParameter,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            var attackRangePoint = characterUnitPos + rangeParameter.ToOutpostCoord();
            var fieldCoordPoint = coordinateConverter.OutpostToFieldCoord(battleSide, attackRangePoint);

            return mstPageModel.ClampByPage(fieldCoordPoint);
        }

        static FieldCoordV2 GetAttackRangePointByKoma(
            BattleSide battleSide,
            OutpostCoordV2 characterUnitPos,
            AttackRangeParameter rangeParameter,
            bool isStartPoint,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            var fieldCoordPos = coordinateConverter.OutpostToFieldCoord(battleSide, characterUnitPos);
            var locatedKomaNo = mstPageModel.GetKomaNoAt(fieldCoordPos);

            if (locatedKomaNo.IsEmpty())
            {
                return FieldCoordV2.Empty;
            }

            var komaNo = battleSide == BattleSide.Player
                ? locatedKomaNo + rangeParameter.ToKomaCount()
                : locatedKomaNo - rangeParameter.ToKomaCount();

            if (komaNo < KomaNo.Zero)
            {
                return new FieldCoordV2(0f, fieldCoordPos.Y);
            }
            if (komaNo > mstPageModel.MaxKomaNo)
            {
                return new FieldCoordV2(mstPageModel.TotalWidth, fieldCoordPos.Y);
            }

            var komaRange = mstPageModel.GetKomaRange(komaNo);

            var attackRangePoint = battleSide == BattleSide.Player
                ? isStartPoint ? komaRange.Min : komaRange.Max
                : isStartPoint ? komaRange.Max : komaRange.Min;

            return new FieldCoordV2(attackRangePoint, fieldCoordPos.Y);
        }

        static FieldCoordV2 GetAttackRangePointByKomaLine(
            BattleSide battleSide,
            OutpostCoordV2 characterUnitPos,
            AttackRangeParameter rangeParameter,
            bool isStartPoint,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            var fieldCoordPos = coordinateConverter.OutpostToFieldCoord(battleSide, characterUnitPos);
            var locatedKomaLineNo = mstPageModel.GetKomaLineNoAt(fieldCoordPos);

            if (locatedKomaLineNo.IsEmpty())
            {
                return FieldCoordV2.Empty;
            }

            var komaLineNo = battleSide == BattleSide.Player
                ? locatedKomaLineNo + rangeParameter.ToKomaLineNo()
                : locatedKomaLineNo - rangeParameter.ToKomaLineNo();

            if (komaLineNo.Value < 0)
            {
                return new FieldCoordV2(0f, fieldCoordPos.Y);
            }
            if (komaLineNo.Value >= mstPageModel.KomaLineCount)
            {
                return new FieldCoordV2(mstPageModel.TotalWidth, fieldCoordPos.Y);
            }

            var komaLineRange = mstPageModel.GetKomaLineRange(komaLineNo);

            var attackRangePoint = battleSide == BattleSide.Player
                ? isStartPoint ? komaLineRange.Min : komaLineRange.Max
                : isStartPoint ? komaLineRange.Max : komaLineRange.Min;

            return new FieldCoordV2(attackRangePoint, fieldCoordPos.Y);
        }

        static FieldCoordV2 GetAttackRangePointByPage(
            BattleSide battleSide,
            OutpostCoordV2 characterUnitPos,
            bool isStartPoint,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter)
        {
            var fieldCoordPos = coordinateConverter.OutpostToFieldCoord(battleSide, characterUnitPos);

            return isStartPoint
                ? new FieldCoordV2(0f, fieldCoordPos.Y)
                : new FieldCoordV2(mstPageModel.TotalWidth, fieldCoordPos.Y);
        }
    }
}
