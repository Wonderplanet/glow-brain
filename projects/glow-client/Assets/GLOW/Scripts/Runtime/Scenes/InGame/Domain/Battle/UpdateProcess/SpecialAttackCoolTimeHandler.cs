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
    public class SpecialAttackCoolTimeHandler : IImmediateEffectHandler
    {
        public (IReadOnlyList<DeckUnitModel> updatedPlayerDeckUnits,
            IReadOnlyList<DeckUnitModel> updatedPvpOpponentDeckUnits,
            IReadOnlyList<AppliedDeckStateEffectResultModel> appliedResults) Handle(
            IAttackResultModel attackResult,
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits)
        {
            // 必殺: HitAttackResultModel（Direct設定想定）
            HitAttackResultModel hitAttackResult = attackResult as HitAttackResultModel;

            var emptyResult = (playerDeckUnits, pvpOpponentDeckUnits, Array.Empty<AppliedDeckStateEffectResultModel>());
            if (hitAttackResult == null) return emptyResult;

            // 盤面上対象
            var targetUnit = characterUnits.FirstOrDefault(u => u.Id == hitAttackResult.TargetId, CharacterUnitModel.Empty);
            if (targetUnit.IsEmpty()) return emptyResult;

            // スペシャルユニットは除外（必殺クールタイムなし）
            if (targetUnit.RoleType == CharacterUnitRoleType.Special) return emptyResult;

            // 陣営に応じて適切なDeckを選択
            var isPlayerSide = targetUnit.BattleSide == BattleSide.Player;
            var targetDeckUnits = isPlayerSide ? playerDeckUnits.ToList(): pvpOpponentDeckUnits.ToList();

            // 盤面外対象: CharacterIdとBattleSideで特定
            var targetDeckUnit = targetDeckUnits.FirstOrDefault(d =>
                d.CharacterId == targetUnit.CharacterId &&
                d.BattleSide == targetUnit.BattleSide,
                DeckUnitModel.Empty);
            if (targetDeckUnit.IsEmpty()) return emptyResult;

            // カウントダウン停止中は弾く(必殺予約(カウントダウン停止)〜カットインまで(カウントダウン再開))
            if (targetUnit.IsSpecialAttackReady())
            {
                return emptyResult;
            }

            // 必殺クールタイム短縮/延長処理
            var stateEffectType = attackResult.StateEffect.Type;
            var parameterTickCount = attackResult.StateEffect.Parameter.ToTickCount();
            var newRemainingTickCount = stateEffectType == StateEffectType.SpecialAttackCoolTimeShorten
                ? TickCount.Max(TickCount.Zero, targetDeckUnit.RemainingSpecialAttackCoolTime - parameterTickCount)
                : TickCount.Max(TickCount.Zero, targetDeckUnit.RemainingSpecialAttackCoolTime + parameterTickCount);
            var updatedDeckUnit = targetDeckUnit with
            {
                RemainingSpecialAttackCoolTime = newRemainingTickCount
            };
            int index = targetDeckUnits.FindIndex(d => d == targetDeckUnit);
            if (index < 0) return emptyResult;   // 念の為
            targetDeckUnits[index] = updatedDeckUnit;

            var appliedResult = new AppliedDeckStateEffectResultModel(
                targetUnit.BattleSide,
                new List<FieldObjectId> { attackResult.AttackerId },
                stateEffectType,
                PercentageM.Empty,
                targetDeckUnit.CharacterId);

            // 陣営に応じて戻り値を構築
            var updatedPlayerDeckUnits = isPlayerSide ? targetDeckUnits : playerDeckUnits;
            var updatedPvpOpponentDeckUnits = isPlayerSide ? pvpOpponentDeckUnits : targetDeckUnits;

            return (updatedPlayerDeckUnits, updatedPvpOpponentDeckUnits, new[] { appliedResult });
        }
    }
}

