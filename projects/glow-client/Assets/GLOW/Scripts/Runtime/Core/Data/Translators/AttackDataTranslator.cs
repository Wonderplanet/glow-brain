using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Data.Translators
{
    public static  class AttackDataTranslator
    {
        public static AttackData ToAttackData(
            MstAttackData mstAttack,
            IReadOnlyList<MstAttackElementData> mstElements,
            IReadOnlyList<MstAttackHitEffectData> mstAttackHitEffectDataList)
        {
            var baseData = new AttackBaseData(
                EnumListTranslator.ToEnumList<CharacterColor>(mstAttack.KillerColors),
                new KillerPercentage(mstAttack.KillerPercentage),
                new TickCount(mstAttack.ActionFrames),
                new TickCount(mstAttack.NextAttackInterval));

            var elements = ToAttackElements(mstElements, mstAttackHitEffectDataList);

            return new AttackData(
                new TickCount(mstAttack.AttackDelay),
                baseData,
                elements);
        }

        static IReadOnlyList<AttackElement> ToAttackElements(
            IReadOnlyList<MstAttackElementData> mstElements,
            IReadOnlyList<MstAttackHitEffectData> mstAttackHitEffectDataList)
        {
            var elements = new List<AttackElement>();

            if (mstElements.Count == 0)
            {
                return elements;
            }

            var sortedMstElements = mstElements.OrderBy(mstElement => mstElement.SortOrder);

            var subElements = new List<AttackSubElement>();
            MstAttackElementData lastNonSubMstElementData = null;

            foreach (var mstElement in sortedMstElements)
            {
                if (mstElement.AttackType == AttackType.None)
                {
                    var hitEffectData = mstAttackHitEffectDataList
                        .FirstOrDefault(data => data.Id == mstElement.HitEffectId);

                    var subElement = AttackElementDataTranslator.ToAttackSubElement(mstElement, hitEffectData);
                    subElements.Add(subElement);
                    continue;
                }

                if (lastNonSubMstElementData != null)
                {
                    var mstAttackHitEffectData = mstAttackHitEffectDataList
                        .FirstOrDefault(data => data.Id == lastNonSubMstElementData.HitEffectId);

                    var element = AttackElementDataTranslator.ToAttackElement(
                        lastNonSubMstElementData,
                        subElements,
                        mstAttackHitEffectData);

                    elements.Add(element);

                    subElements = new List<AttackSubElement>();
                }

                lastNonSubMstElementData = mstElement;
            }

            if (lastNonSubMstElementData != null)
            {
                var hitEffectData = mstAttackHitEffectDataList
                    .FirstOrDefault(data => data.Id == lastNonSubMstElementData.HitEffectId);

                var element = AttackElementDataTranslator.ToAttackElement(
                    lastNonSubMstElementData,
                    subElements,
                    hitEffectData);

                elements.Add(element);
            }

            return elements;
        }
    }
}
