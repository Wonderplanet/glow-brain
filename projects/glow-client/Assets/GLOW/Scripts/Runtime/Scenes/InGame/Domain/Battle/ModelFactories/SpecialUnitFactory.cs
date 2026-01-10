using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    /// <summary> ロールがスペシャルのユニットのFactory </summary>
    public class SpecialUnitFactory : ISpecialUnitFactory
    {
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IFieldObjectIdProvider FieldObjectIdProvider { get; }
        [Inject] IStateEffectSourceIdProvider StateEffectSourceIdProvider { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject(Id = InGameUnitEncyclopediaBindIds.Player)]
        IInGameUnitEncyclopediaEffectProvider UnitEncyclopediaEffectProvider { get; }
        [Inject(Id = InGameUnitEncyclopediaBindIds.PvpOpponent)]
        IInGameUnitEncyclopediaEffectProvider PvpOpponentUnitEncyclopediaEffectProvider { get; }
        [Inject] IInGameEventBonusUnitEffectProvider InGameEventBonusUnitEffectProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] ISpecialRoleSpecialAttackFactory SpecialRoleSpecialAttackFactory { get; }

        public SpecialUnitModel GenerateSpecialUnit(
            MstCharacterModel mstCharacter,
            BattleSide battleSide,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page,
            PageCoordV2 pageCoordPos)
        {
            if (battleSide == BattleSide.Player)
            {
                var userUnit =
                    GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.MstUnitId == mstCharacter.Id);

                return GenerateSpecialUnit(
                    mstCharacter,
                    battleSide,
                    userUnit.Level,
                    userUnit.Rank,
                    userUnit.Grade,
                    komaDictionary,
                    page,
                    pageCoordPos,
                    UnitEncyclopediaEffectProvider);
            }
            else
            {
                var opponentStatus = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();
                var pvpUnit = opponentStatus.PvpUnits.Find(unit => unit.MstUnitId == mstCharacter.Id);
                return GenerateSpecialUnit(
                    mstCharacter,
                    battleSide,
                    pvpUnit.UnitLevel,
                    pvpUnit.UnitRank,
                    pvpUnit.UnitGrade,
                    komaDictionary,
                    page,
                    pageCoordPos,
                    PvpOpponentUnitEncyclopediaEffectProvider);
            }
        }

        SpecialUnitModel GenerateSpecialUnit(
            MstCharacterModel mstCharacter,
            BattleSide battleSide,
            UnitLevel unitLevel,
            UnitRank unitRank,
            UnitGrade unitGrade,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page,
            PageCoordV2 pageCoordPos,
            IInGameUnitEncyclopediaEffectProvider useUnitEncyclopediaEffectProvider)
        {
            var id = FieldObjectIdProvider.GenerateNewId();
            var stateEffectSourceId = StateEffectSourceIdProvider.GenerateNewId();

            // 所属コマのModel取得
            var fieldCoord = CoordinateConverter.PageToFieldCoord(pageCoordPos);
            var komaNo = page.GetKomaNoAt(fieldCoord);
            var mstKomaModel = page.GetKoma(komaNo);
            var komaModel = komaDictionary.GetValueOrDefault(mstKomaModel.KomaId, KomaModel.Empty);

            // 攻撃力、回復力計算
            PercentageM eventBonus;
            if (InGameScene.Type == InGameType.AdventBattle)
            {
                eventBonus = InGameEventBonusUnitEffectProvider.GetUnitEventBonusPercentageM(
                    mstCharacter.Id,
                    InGameScene.EventBonusGroupId);
            }
            else
            {
                eventBonus = InGameEventBonusUnitEffectProvider.GetUnitEventBonusPercentageM(
                    mstCharacter.Id,
                    InGameScene.MstQuest.Id);
            }

            var calculateStatus = UnitStatusCalculateHelper.Calculate(
                mstCharacter,
                unitLevel,
                unitRank,
                unitGrade);

            var attackPower =
                calculateStatus.AttackPower * useUnitEncyclopediaEffectProvider.GetAttackPowerEffectPercentage() * eventBonus;

            var healPower = HealPower.FromPercentageM(useUnitEncyclopediaEffectProvider.GetHealEffectPercentage());

            // 引数のpageCoordPosはそのままだとタッチした位置になってしまうため、現在いるコマの右端位置を取得した後にhalfWidthを足して中心位置に調整
            (var komaWidth, var komaFrontBorderPoint) = page.GetKomaWidthAndFrontPosAtPos(fieldCoord);
            var centerPos = komaFrontBorderPoint with { X = komaFrontBorderPoint.X + komaWidth / 2f };
            var outpostCoordTargetPoint = CoordinateConverter.FieldToOutpostCoord(battleSide, centerPos);

            // 必殺ワザ
            var specialAttackData = SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                mstCharacter,
                unitGrade,
                unitLevel);

            // SpeechBalloon設定
            var speechBalloons = mstCharacter.SpeechBalloons
                .Select(balloon => new UnitSpeechBalloonModel(id, balloon))
                .ToList();

            // 必殺技チャージ開始までの時間算出。召喚演出終わりにチャージ開始するのでその時間まで
            var showTime = SpecialUnitModel.ShowHideTime;
            var remainingTimeUntilSpecialAttackCharge = showTime;

            // 必殺技発動までの残り時間算出
            var remainingTimeUntilSpecialAttack =
                remainingTimeUntilSpecialAttackCharge + CharacterUnitAttackChargeAction.InitialChargeTime;

            // 必殺技演出が終了し効果が発動するまでの時間(TickCount(3)は演出開始から解除タイミングまでの調整値、specialAttackData.AttackDelay分遅延させる)
            var remainingTimeEndSpecialAttack =
                remainingTimeUntilSpecialAttack + new TickCount(3) + specialAttackData.AttackDelay;

            // 召喚から退去終了までの時間算出(出現と退去のFade + 必殺技溜め時間 + 必殺後の退去までの待機時間 + specialAttackData.AttackDelay)
            // 必殺技演出中はポーズをかけてTickCountを更新しないため除外する
            var remainingLeavingTime =
                showTime * 2 + CharacterUnitAttackChargeAction.InitialChargeTime + SpecialUnitModel.EndSpecialAttackedWaitTime + specialAttackData.AttackDelay;

            return new SpecialUnitModel(
                id,
                stateEffectSourceId,
                mstCharacter.Id,
                battleSide,
                mstCharacter.Color,
                mstCharacter.AssetKey,
                attackPower,
                healPower,
                mstCharacter.ColorAdvantageAttackBonus,
                specialAttackData,
                Array.Empty<IStateEffectModel>(),
                speechBalloons,
                komaModel,
                outpostCoordTargetPoint,
                remainingTimeUntilSpecialAttackCharge,
                remainingTimeUntilSpecialAttack,
                remainingTimeEndSpecialAttack,
                remainingLeavingTime,
                SpecialUnitSpecialAttackChargeFlag.False,
                SpecialUnitUseSpecialAttackFlag.False);
        }
    }
}
