using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class SummonCoolTimeHandler : IImmediateEffectHandler
    {
        public (IReadOnlyList<DeckUnitModel> updatedPlayerDeckUnits,
            IReadOnlyList<DeckUnitModel> updatedPvpOpponentDeckUnits,
            IReadOnlyList<AppliedDeckStateEffectResultModel> appliedResults) Handle(
            IAttackResultModel attackResult,
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits)
        {
            // 召喚: DeckAttackResultModel（Deck設定想定）
            DeckAttackResultModel deckAttackResult = attackResult as DeckAttackResultModel;

            var emptyResult = (playerDeckUnits, pvpOpponentDeckUnits, Array.Empty<AppliedDeckStateEffectResultModel>());
            if (deckAttackResult == null) return emptyResult;

            // 陣営に応じて適切なDeckを選択
            var isPlayerSide = deckAttackResult.TargetBattleSide == BattleSide.Player;
            var targetDeckUnits = isPlayerSide ? playerDeckUnits.ToList() : pvpOpponentDeckUnits.ToList();

            // 未召喚のスペシャルを除くキャラに対してCharacterIdとBattleSideで抽出
            var targetDeckUnit = FindTargetDeckUnit(
                targetDeckUnits,
                deckAttackResult.TargetCharacterId,
                deckAttackResult.TargetBattleSide);
            if (targetDeckUnit.IsEmpty()) return emptyResult;

            // 召喚クールタイム短縮/延長処理
            var stateEffectType = attackResult.StateEffect.Type;
            var parameterTickCount = attackResult.StateEffect.Parameter.ToTickCount();
            var newRemainingTickCount = stateEffectType == StateEffectType.SummonCoolTimeShorten
                ? TickCount.Max(TickCount.Zero, targetDeckUnit.RemainingSummonCoolTime - parameterTickCount)
                : TickCount.Max(TickCount.Zero, targetDeckUnit.RemainingSummonCoolTime + parameterTickCount);
            var updatedDeckUnit = targetDeckUnit with
            {
                RemainingSummonCoolTime = newRemainingTickCount
            };

            var index = targetDeckUnits.FindIndex(d => d == targetDeckUnit);
            if (index < 0) return emptyResult;   // 念の為
            targetDeckUnits[index] = updatedDeckUnit;

            var appliedResult = new AppliedDeckStateEffectResultModel(
                deckAttackResult.TargetBattleSide,
                new List<FieldObjectId> { attackResult.AttackerId },
                stateEffectType,
                PercentageM.Empty,
                targetDeckUnit.CharacterId);

            // 陣営に応じて戻り値を構築
            var updatedPlayerDeckUnits = isPlayerSide ? targetDeckUnits : playerDeckUnits;
            var updatedPvpOpponentDeckUnits = isPlayerSide ? pvpOpponentDeckUnits : targetDeckUnits;

            return (updatedPlayerDeckUnits, updatedPvpOpponentDeckUnits, new[] { appliedResult });
        }

        DeckUnitModel FindTargetDeckUnit(
            List<DeckUnitModel> deckUnits,
            MasterDataId targetCharacterId,
            BattleSide targetBattleSide
        )
        {
            // 未召喚のスペシャルを除くキャラに対してCharacterIdとBattleSideで抽出
            return deckUnits.FirstOrDefault(d =>
                    !d.IsSummoned &&
                    d.RoleType != CharacterUnitRoleType.Special &&
                    d.CharacterId == targetCharacterId &&
                    d.BattleSide == targetBattleSide,
                DeckUnitModel.Empty
            );
        }
    }
}

