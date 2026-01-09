using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record SpecialUnitModel(
        FieldObjectId Id,
        StateEffectSourceId StateEffectSourceId,
        MasterDataId CharacterId,
        BattleSide BattleSide,
        CharacterColor Color,
        UnitAssetKey AssetKey,
        AttackPower AttackPower,
        HealPower HealPower,
        CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus,
        AttackData SpecialAttack,
        IReadOnlyList<IStateEffectModel> StateEffects,
        IReadOnlyList<UnitSpeechBalloonModel> SpeechBalloons,
        KomaModel LocatedKoma, // 現在いるコマのKomaModel
        OutpostCoordV2 Pos,
        TickCount RemainingTimeUntilSpecialAttackCharge,  // 必殺技チャージ開始までの時間
        TickCount RemainingTimeUntilSpecialAttack,  // 必殺技発動までの残り時間
        TickCount RemainingTimeEndSpecialAttack,    // 必殺技演出が終了し効果が発動するまでの残り時間
        TickCount RemainingLeavingTime,             // 退去までの時間
        SpecialUnitSpecialAttackChargeFlag SpecialUnitSpecialAttackChargeFlag, // デッキアイコンの表示切り替えタイミングに使用
        SpecialUnitUseSpecialAttackFlag SpecialUnitUseSpecialAttackFlag) // カットイン発動タイミングに使用
    {
        public static readonly TickCount ShowHideTime = new TickCount(60); // 召喚・退去時の所要時間とする時間
        public static readonly TickCount EndSpecialAttackedWaitTime = new TickCount(50); // 必殺技発動後のスペシャル特有の待機時間

        public static SpecialUnitModel Empty { get; } = new(
            FieldObjectId.Empty,
            StateEffectSourceId.Empty,
            MasterDataId.Empty,
            BattleSide.Player,
            CharacterColor.None,
            UnitAssetKey.Empty,
            AttackPower.Empty,
            HealPower.Empty,
            CharacterColorAdvantageAttackBonus.Empty,
            AttackData.Empty,
            Array.Empty<IStateEffectModel>(),
            Array.Empty<UnitSpeechBalloonModel>(),
            KomaModel.Empty,
            OutpostCoordV2.Empty,
            TickCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            TickCount.Empty,
            SpecialUnitSpecialAttackChargeFlag.False,
            SpecialUnitUseSpecialAttackFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
