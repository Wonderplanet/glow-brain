using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckSpecialUnitSummonPositionShifter : IDeckSpecialUnitSummonPositionShifter
    {
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }

        /// <summary>
        /// 指定した召喚位置のコマが召喚不可の場合、そのコマにできるだけ近い召喚可能コマの中心位置を返す
        /// 指定した召喚位置のコマが召喚可能な場合はその召喚位置をそのまま返す
        /// </summary>
        public PageCoordV2 ShiftSummonPosition(
            PageCoordV2 pos,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            ICoordinateConverter coordinateConverter,
            BattleSide battleSide)
        {
            var fieldCoordTargetPoint = coordinateConverter.PageToFieldCoord(pos);
            var targetKomaNo = mstPage.GetKomaNoAt(fieldCoordTargetPoint);

            // 指定位置のコマが召喚可能な場合はそのまま返す
            if (DeckSpecialUnitSummonEvaluator.IsSummonableKoma(
                    targetKomaNo,
                    specialUnitSummonInfoModel,
                    mstPage,
                    komaDictionary,
                    battleSide))
            {
                return pos;
            }

            // 召喚しようとするコマから一番遠いコマまでのコマ数
            var komaShiftDistance = KomaCount.Max(targetKomaNo.ToKomaCount(), mstPage.MaxKomaNo - targetKomaNo);

            // 召喚しようとするコマに近いコマから召喚可能コマを探す
            for (var i = 1; i <= komaShiftDistance.Value; i++)
            {
                var shiftedKomaNo = ShiftSummonKoma(
                    targetKomaNo,
                    new KomaCount(i),
                    specialUnitSummonInfoModel,
                    mstPage,
                    komaDictionary,
                    battleSide);

                if (!shiftedKomaNo.IsEmpty())
                {
                    var komaRange = mstPage.GetKomaRange(shiftedKomaNo);
                    var komaCenter = mstPage.GetKomaHeight(shiftedKomaNo) * 0.5f;
                    var fieldCoordCenterPos = new FieldCoordV2(komaRange.Center, komaCenter);
                    return coordinateConverter.FieldToPageCoord(fieldCoordCenterPos);
                }
            }

            return PageCoordV2.Empty;
        }

        KomaNo ShiftSummonKoma(
            KomaNo targetKomaNo,
            KomaCount komaShiftDistance,
            SpecialUnitSummonInfoModel specialUnitSummonInfoModel,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            BattleSide battleSide)
        {
            var rightKomaNo = targetKomaNo - komaShiftDistance;
            if (rightKomaNo >= KomaNo.Zero)
            {
                if (DeckSpecialUnitSummonEvaluator.IsSummonableKoma(
                    rightKomaNo,
                    specialUnitSummonInfoModel,
                    mstPage,
                    komaDictionary,
                    battleSide))
                {
                    return rightKomaNo;
                }
            }

            var leftKomaNo = targetKomaNo + komaShiftDistance;
            if (leftKomaNo <= mstPage.MaxKomaNo)
            {
                if (DeckSpecialUnitSummonEvaluator.IsSummonableKoma(
                    leftKomaNo,
                    specialUnitSummonInfoModel,
                    mstPage,
                    komaDictionary,
                    battleSide))
                {
                    return leftKomaNo;
                }
            }

            return KomaNo.Empty;
        }
    }
}
