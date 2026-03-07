#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Debugs.InGame.Domain.Translators
{
    // DebugAttackElementData → AttackElement/AttackSubElement 変換
    // AttackElementDataTranslatorの変換ロジックをに合わせて実装
    public static class DebugAttackElementDataTranslator
    {
        // DebugAttackElementDataリスト → AttackElementリスト一括変換
        // MainとSubの関係を解決して変換する
        public static IReadOnlyList<AttackElement> ToAttackElements(
            IReadOnlyList<DebugAttackElementData> attackElementDataList)
        {
            var elements = new List<AttackElement>();

            if (attackElementDataList.Count == 0)
            {
                return elements;
            }

            // SortOrderでソート
            var sortedElements = attackElementDataList.OrderBy(data => data.SortOrder);

            var subElements = new List<AttackSubElement>();
            DebugAttackElementData lastNonSubElementData = null;

            foreach (var elementData in sortedElements)
            {
                if (elementData.AttackType == AttackType.None)
                {
                    // SubElementとして収集
                    var subElement = ToAttackSubElement(elementData);
                    subElements.Add(subElement);
                    continue;
                }

                if (lastNonSubElementData != null)
                {
                    // 前のMainElementを変換して追加
                    var element = ToAttackElement(lastNonSubElementData, subElements);
                    elements.Add(element);

                    subElements = new List<AttackSubElement>();
                }

                lastNonSubElementData = elementData;
            }

            // 最後のMainElementを処理
            if (lastNonSubElementData != null)
            {
                var element = ToAttackElement(lastNonSubElementData, subElements);
                elements.Add(element);
            }

            return elements;
        }

        // DebugAttackElementData → AttackElement 変換
        public static AttackElement ToAttackElement(
            DebugAttackElementData data,
            IReadOnlyList<AttackSubElement> subElements)
        {
            var effect = CreateStateEffect(
                data.EffectType,
                data.EffectiveCount,
                data.EffectiveDuration,
                data.EffectParameter,
                data.EffectValue,
                data.EffectTriggerRoles,
                data.EffectTriggerColors);

            var hitData = new AttackHitData(
                data.HitType,
                new AttackHitParameter(data.HitParameter1),
                new AttackHitParameter(data.HitParameter2),
                AttackHitBattleEffectId.Empty,
                new List<AttackHitOnomatopoeiaAssetKey>(),
                SoundEffectAssetKey.Empty,
                SoundEffectAssetKey.Empty,
                AccumulatedDamageKnockBackFlag.True);

            return new AttackElement(
                new MasterDataId(data.Id),
                new TickCount(data.AttackDelay),
                TickCount.Empty,
                data.AttackType,
                new AttackRange(
                    data.RangeStartType,
                    new AttackRangeParameter(data.RangeStartParameter),
                    data.RangeEndType,
                    new AttackRangeParameter(data.RangeEndParameter)),
                data.MaxTargetCount < 0
                    ? FieldObjectCount.Infinity
                    : new FieldObjectCount(data.MaxTargetCount),
                AttackViewId.Empty,
                data.Target,
                data.TargetType,
                EnumListTranslator.ToEnumList<CharacterColor>(data.TargetColors),
                EnumListTranslator.ToEnumList<CharacterUnitRoleType>(data.TargetRoles),
                MasterDataIdListTranslator.ToMasterDataIdList(data.TargetMstSeriesIds),
                MasterDataIdListTranslator.ToMasterDataIdList(data.TargetMstCharacterIds),
                data.DamageType,
                hitData,
                new AttackHitStopFlag(data.IsHitStop),
                new Percentage(data.Probability),
                new AttackPowerParameter(data.PowerParameterType, data.PowerParameter),
                effect,
                subElements);
        }

        // DebugAttackElementData → AttackSubElement 変換
        public static AttackSubElement ToAttackSubElement(DebugAttackElementData data)
        {
            if (data.AttackType != AttackType.None)
            {
                throw new InvalidOperationException(
                    $"SubElement must have AttackType.None: {data.Id}");
            }

            var effect = CreateStateEffect(
                data.EffectType,
                data.EffectiveCount,
                data.EffectiveDuration,
                data.EffectParameter,
                data.EffectValue,
                string.Empty,
                string.Empty);

            var hitData = new AttackHitData(
                data.HitType,
                new AttackHitParameter(data.HitParameter1),
                new AttackHitParameter(data.HitParameter2),
                AttackHitBattleEffectId.Empty,
                new List<AttackHitOnomatopoeiaAssetKey>(),
                SoundEffectAssetKey.Empty,
                SoundEffectAssetKey.Empty,
                AccumulatedDamageKnockBackFlag.True);

            return new AttackSubElement(
                new MasterDataId(data.Id),
                data.TargetType,
                EnumListTranslator.ToEnumList<CharacterColor>(data.TargetColors),
                EnumListTranslator.ToEnumList<CharacterUnitRoleType>(data.TargetRoles),
                MasterDataIdListTranslator.ToMasterDataIdList(data.TargetMstSeriesIds),
                MasterDataIdListTranslator.ToMasterDataIdList(data.TargetMstCharacterIds),
                data.DamageType,
                hitData,
                new Percentage(data.Probability),
                new AttackPowerParameter(data.PowerParameterType, data.PowerParameter),
                effect);
        }

        static StateEffect CreateStateEffect(
            StateEffectType effectType,
            int effectiveCount,
            int effectiveDuration,
            float effectParameter,
            string effectValue,
            string effectTriggerRoles,
            string effectTriggerColors)
        {
            if (effectType == StateEffectType.None)
            {
                return StateEffect.Empty;
            }

            var conditionRole = string.IsNullOrEmpty(effectTriggerRoles)
                ? StateEffectConditionValue.Empty
                : new StateEffectConditionValue(effectTriggerRoles);

            var conditionColor = string.IsNullOrEmpty(effectTriggerColors)
                ? StateEffectConditionValue.Empty
                : new StateEffectConditionValue(effectTriggerColors);

            return new StateEffect(
                effectType,
                effectiveCount < 0
                    ? EffectiveCount.Infinity
                    : new EffectiveCount(effectiveCount),
                EffectiveProbability.Hundred,
                effectiveDuration < 0
                    ? TickCount.Infinity
                    : new TickCount(effectiveDuration),
                new StateEffectParameter((decimal)effectParameter),
                new StateEffectValue(effectValue),
                conditionRole,
                conditionColor);
        }
    }
}
#endif


