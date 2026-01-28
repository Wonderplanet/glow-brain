using System;
using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class AttackElementDataTranslator
    {
        public static AttackElement ToAttackElement(
            MstAttackElementData data,
            IReadOnlyList<AttackSubElement> subElements,
            MstAttackHitEffectData mstAttackHitEffectData)
        {
            var hitData = CreateAttackHitData(data, mstAttackHitEffectData);

            var effect = data.EffectType == StateEffectType.None
                ? StateEffect.Empty
                : new StateEffect(
                    data.EffectType,
                    data.EffectiveCount < 0
                        ? EffectiveCount.Infinity
                        : new EffectiveCount(data.EffectiveCount),
                    EffectiveProbability.Hundred,
                    data.EffectiveDuration < 0
                        ? TickCount.Infinity
                        : new TickCount(data.EffectiveDuration),
                    new StateEffectParameter((decimal)data.EffectParameter),
                    StateEffectConditionValue.Empty,
                    StateEffectConditionValue.Empty);

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

        public static AttackSubElement ToAttackSubElement(
            MstAttackElementData data,
            MstAttackHitEffectData mstAttackHitEffectData)
        {
            if (data.AttackType != AttackType.None)
            {
                throw new InvalidOperationException(ZString.Format("This method is only for AttackType.None : AttackElementId={0}", data.Id));
            }

            var hitData = CreateAttackHitData(data, mstAttackHitEffectData);

            var effect = data.EffectType == StateEffectType.None
                ? StateEffect.Empty
                : new StateEffect(
                    data.EffectType,
                    data.EffectiveCount < 0
                        ? EffectiveCount.Infinity
                        : new EffectiveCount(data.EffectiveCount),
                    EffectiveProbability.Hundred,
                    data.EffectiveDuration < 0
                        ? TickCount.Infinity
                        : new TickCount(data.EffectiveDuration),
                    new StateEffectParameter((decimal)data.EffectParameter),
                    StateEffectConditionValue.Empty,
                    StateEffectConditionValue.Empty);

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

        static AttackHitData CreateAttackHitData(MstAttackElementData data, MstAttackHitEffectData mstAttackHitEffectData)
        {
            var soundEffectAssetKey = string.IsNullOrEmpty(mstAttackHitEffectData?.SoundEffectAssetKey)
                ? SoundEffectAssetKey.Empty
                : new SoundEffectAssetKey(mstAttackHitEffectData.SoundEffectAssetKey);
            
            var killerSoundEffectAssetKey = string.IsNullOrEmpty(mstAttackHitEffectData?.KillerSoundEffectAssetKey)
                ? SoundEffectAssetKey.Empty
                : new SoundEffectAssetKey(mstAttackHitEffectData.KillerSoundEffectAssetKey);
                
            return new AttackHitData(
                data.HitType,
                new AttackHitParameter(data.HitParameter1),
                new AttackHitParameter(data.HitParameter2),
                AttackHitBattleEffectId.Empty,
                CreateAttackHitOnomatopoeiaAssetKeys(mstAttackHitEffectData),
                soundEffectAssetKey,
                killerSoundEffectAssetKey,
                AccumulatedDamageKnockBackFlag.True);
        }

        static List<AttackHitOnomatopoeiaAssetKey> CreateAttackHitOnomatopoeiaAssetKeys(MstAttackHitEffectData data)
        {
            var assetKeys = new List<AttackHitOnomatopoeiaAssetKey>();
            if (data == null) return assetKeys;

            if (!string.IsNullOrEmpty(data.Onomatopoeia1AssetKey))
            {
                assetKeys.Add(new AttackHitOnomatopoeiaAssetKey(data.Onomatopoeia1AssetKey));
            }

            if (!string.IsNullOrEmpty(data.Onomatopoeia2AssetKey))
            {
                assetKeys.Add(new AttackHitOnomatopoeiaAssetKey(data.Onomatopoeia2AssetKey));
            }

            if (!string.IsNullOrEmpty(data.Onomatopoeia3AssetKey))
            {
                assetKeys.Add(new AttackHitOnomatopoeiaAssetKey(data.Onomatopoeia3AssetKey));
            }

            return assetKeys;
        }
    }
}
