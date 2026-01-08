using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.InGame.Domain.Battle.CharacterUnitAction;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class CharacterUnitFactory : ICharacterUnitFactory
    {
        [Inject] IFieldObjectIdProvider FieldObjectIdProvider { get; }
        [Inject] IStateEffectSourceIdProvider StateEffectSourceIdProvider { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject(Id = InGameUnitEncyclopediaBindIds.Player)]
        IInGameUnitEncyclopediaEffectProvider UnitEncyclopediaEffectProvider { get; }
        [Inject(Id = InGameUnitEncyclopediaBindIds.PvpOpponent)]
        IInGameUnitEncyclopediaEffectProvider PvpOpponentUnitEncyclopediaEffectProvider { get; }
        [Inject] IMarchingLaneDirector MarchingLaneDirector { get; }
        [Inject] ICommonConditionModelFactory CommonConditionModelFactory { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IStateEffectModelFactory StateEffectModelFactory { get; }
        [Inject] ISpecialRoleSpecialAttackFactory SpecialRoleSpecialAttackFactory { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterRepository { get; }
        [Inject] IInGameUnitStatusCalculator UnitStatusCalculator { get; }
        [Inject] IUnitAbilityModelFactory UnitAbilityModelFactory { get; }

        public CharacterUnitModel GenerateUserCharacterUnit(
            MstCharacterModel mstCharacter,
            BattleSide side,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page)
        {
            var fetchOther = GameRepository.GetGameFetchOther();
            var userUnit = fetchOther.UserUnitModels.Find(unit => unit.MstUnitId == mstCharacter.Id);

            if(fetchOther.TutorialStatus == TutorialSequenceIdDefinitions.TutorialStartIntroduction)
            {
                // 導入チュートリアル中はユーザーユニットを上書きする
                userUnit = new UserUnitModel(
                    MasterDataId.Empty,
                    UserDataId.Empty,
                    new UnitLevel(10),
                    new UnitRank(1),
                    new UnitGrade(1),
                    NewEncyclopediaFlag.False);
            }

            return GenerateCharacterUnit(
                mstCharacter,
                userUnit.Level,
                userUnit.Rank,
                userUnit.Grade,
                side,
                komaDictionary,
                page,
                UnitEncyclopediaEffectProvider);
        }

        public CharacterUnitModel GenerateOpponentCharacterUnit(
            MstCharacterModel mstCharacter,
            BattleSide side,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page)
        {
            var opponentStatus = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();
            var pvpUnit = opponentStatus.PvpUnits.Find(unit => unit.MstUnitId == mstCharacter.Id);

            return GenerateCharacterUnit(
                mstCharacter,
                pvpUnit.UnitLevel,
                pvpUnit.UnitRank,
                pvpUnit.UnitGrade,
                side,
                komaDictionary,
                page,
                PvpOpponentUnitEncyclopediaEffectProvider);
        }

        public CharacterUnitModel GenerateEnemyCharacterUnit(
            MstEnemyStageParameterModel stageParameter,
            UnitGenerationModel unitGenerationModel,
            BattleSide side,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page)
        {
            var currentTickCount = InGameScene.StageTimeModel.CurrentTickCount;

            var id = FieldObjectIdProvider.GenerateNewId();
            var stateEffectSourceId = StateEffectSourceIdProvider.GenerateNewId();

            var marchingLane = MarchingLaneDirector.AssignLane(
                id,
                side,
                stageParameter.IsBoss,
                unitGenerationModel.MarchingLane);

            var pos = unitGenerationModel.SummonPosition.IsEmpty()
                ? InGameConstants.DefaultSummonPos
                : CoordinateConverter.FieldToEnemyOutpostCoord(unitGenerationModel.SummonPosition);

            var locateKomaId = page.GetKomaIdAt(CoordinateConverter.OutpostToFieldCoord(side, pos));
            var locateKomaModel = komaDictionary.GetValueOrDefault(locateKomaId, KomaModel.Empty);

            var dropBattlePoint = unitGenerationModel.OverrideDropBattlePoint.IsEmpty()
                ? stageParameter.DropBattlePoint : unitGenerationModel.OverrideDropBattlePoint;

            var unitCoef = unitGenerationModel.UnitCoef;
            var hp = stageParameter.Hp * unitCoef.StageHpCoef * unitCoef.InGameSequenceHpCoef;

            var attackPower = stageParameter.AttackPower.Value
                              * (decimal)unitCoef.StageAttackPowerCoef
                              * (decimal)unitCoef.InGameSequenceAttackPowerCoef;

            var moveSpeed = stageParameter.UnitMoveSpeed.Value
                            * unitCoef.StageUnitMoveSpeedCoef
                            * unitCoef.InGameSequenceUnitMoveSpeedCoef;

            var action = new CharacterUnitSummoningAction(GetSummoningRemainingTime(
                unitGenerationModel.SummonAnimationType,
                stageParameter.IsBoss));

            var transformationCondition = CommonConditionModelFactory.Create(
                stageParameter.TransformationParameter.GetCommonConditionType(),
                stageParameter.TransformationParameter.ConditionValue.ToCommonConditionValue());

            var transformation = new UnitTransformationModel(
                stageParameter.TransformationParameter.MstEnemyStageParameterId,
                transformationCondition,
                unitGenerationModel.BeforeTransformationFieldObjectId,
                UnitTransformationFinishFlag.False);

            var ability = UnitAbilityModelFactory.Create(stageParameter.Ability);
            var stateEffects = new List<IStateEffectModel>();
            // 常時発動する特性の状態変化を付与する
            if (ability.ArisesStateEffectOnceOnSummon)
            {
                var effectModel = StateEffectModelFactory.Create(
                    ability.StateEffectSourceId,
                    ability.GetStateEffect(),
                    false);

                stateEffects.Add(effectModel);
            }

            var appearanceAttack = unitGenerationModel.IsAppearanceAttackEnabled
                ? stageParameter.AppearanceAttack
                : AttackData.Empty;

            var enemyCharacterModel = MstEnemyCharacterRepository.GetEnemyCharacter(stageParameter.MstEnemyCharacterId);

            return new CharacterUnitModel(
                id,
                stateEffectSourceId,
                unitGenerationModel.AutoPlayerSequenceElementId,
                FieldObjectType.Character,
                side,
                stageParameter.Kind,
                Rarity.R,
                stageParameter.RoleType,
                stageParameter.Color,
                stageParameter.MstEnemyCharacterId,
                enemyCharacterModel.MstSeriesId,
                stageParameter.Id,
                stageParameter.AssetKey,
                unitCoef,
                hp,
                hp,
                stageParameter.DamageKnockBackCount,
                new UnitMoveSpeed(Mathf.CeilToInt(moveSpeed)),
                stageParameter.WellDistance,
                new AttackPower(attackPower),
                HealPower.Default,
                stageParameter.ColorAdvantageAttackBonus,
                stageParameter.ColorAdvantageDefenseBonus,
                stageParameter.NormalAttack,
                stageParameter.SpecialAttack,
                appearanceAttack,
                stageParameter.AttackComboCycle,
                AttackComboCount.Zero,
                TickCount.Zero,
                appearanceAttack.IsEmpty() ? AttackKind.Normal : AttackKind.Appearance,
                new List<UnitAbilityModel>(){ ability },
                action,
                UnitActionState.Move,
                marchingLane,
                pos,
                pos,
                unitGenerationModel.MoveStartCondition,
                unitGenerationModel.MoveStopCondition,
                unitGenerationModel.MoveRestartCondition,
                unitGenerationModel.RemainingMoveLoopCount,
                locateKomaModel,
                KomaModel.Empty,
                KomaModel.Empty,
                currentTickCount,
                currentTickCount,
                TickCount.Empty,
                stateEffects,
                TickCount.Empty,
                unitGenerationModel.SummonAnimationType,
                new List<UnitSpeechBalloonModel>(),
                transformation,
                dropBattlePoint,
                false,
                MoveStoppedFlag.True,
                MoveStoppedFlag.True,
                IsDefeatTarget(stageParameter.MstEnemyCharacterId),
                unitGenerationModel.IsOutpostDamageInvalidation,
                enemyCharacterModel.IsPhantomized,
                unitGenerationModel.AuraType,
                unitGenerationModel.DeathType,
                unitGenerationModel.DefeatedScore);
        }

        CharacterUnitModel GenerateCharacterUnit(
            MstCharacterModel mstCharacter,
            UnitLevel unitLevel,
            UnitRank unitRank,
            UnitGrade unitGrade,
            BattleSide side,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page,
            IInGameUnitEncyclopediaEffectProvider unitEncyclopediaEffectProvider)
        {
            var currentTickCount = InGameScene.StageTimeModel.CurrentTickCount;

            var id = FieldObjectIdProvider.GenerateNewId();
            var stateEffectSourceId = StateEffectSourceIdProvider.GenerateNewId();

            var marchingLane = MarchingLaneDirector.AssignLane(id, side, false, MarchingLaneIdentifier.Empty);
            var pos = InGameConstants.DefaultSummonPos;
            var locateKomaId = page.GetKomaIdAt(CoordinateConverter.OutpostToFieldCoord(side, pos));
            var locateKomaModel = komaDictionary.GetValueOrDefault(locateKomaId, KomaModel.Empty);

            var calculateStatus = UnitStatusCalculateHelper.Calculate(
                mstCharacter,
                unitLevel,
                unitRank,
                unitGrade);

            var statusWithBonus = UnitStatusCalculator.CalculateStatus(
                calculateStatus,
                mstCharacter.Id,
                unitEncyclopediaEffectProvider,
                InGameScene.Type,
                InGameScene.MstQuest.Id,
                InGameScene.EventBonusGroupId,
                InGameScene.SpecialRuleUnitStatusModels);

            var hp = statusWithBonus.HP;
            var attackPower = statusWithBonus.AttackPower;

            var healPower = HealPower.FromPercentageM(unitEncyclopediaEffectProvider.GetHealEffectPercentage());

            var specialAttackData = SpecialRoleSpecialAttackFactory.CreateSpecialRoleSpecialAttack(
                mstCharacter,
                unitGrade,
                unitLevel);

            var speechBalloons = mstCharacter.SpeechBalloons
                .Select(balloon => new UnitSpeechBalloonModel(id, balloon))
                .ToList();

            var abilities = mstCharacter.GetUnlockedMstUnitAbilityModels(unitRank)
                .Select(mstAbility => mstAbility.UnitAbility)
                .Select(unitAbility => UnitAbilityModelFactory.Create(unitAbility))
                .ToList();

            var stateEffects = new List<IStateEffectModel>();
            // 常時発動する特性の状態変化を付与する
            foreach (var ability in abilities)
            {
                if (ability.ArisesStateEffectOnceOnSummon)
                {
                    var effectModel = StateEffectModelFactory.Create(
                        ability.StateEffectSourceId,
                        ability.GetStateEffect(),
                        false);
                    stateEffects.Add(effectModel);
                }
            }

            return new CharacterUnitModel(
                id,
                stateEffectSourceId,
                AutoPlayerSequenceElementId.Empty,
                FieldObjectType.Character,
                side,
                CharacterUnitKind.Normal,
                mstCharacter.Rarity,
                mstCharacter.RoleType,
                mstCharacter.Color,
                mstCharacter.Id,
                mstCharacter.MstSeriesId,
                MasterDataId.Empty,
                mstCharacter.AssetKey,
                InitialCharacterUnitCoef.Empty,
                hp,
                hp,
                mstCharacter.DamageKnockBackCount,
                mstCharacter.UnitMoveSpeed,
                mstCharacter.WellDistance,
                attackPower,
                healPower,
                mstCharacter.ColorAdvantageAttackBonus,
                mstCharacter.ColorAdvantageDefenseBonus,
                mstCharacter.NormalMstAttackModel.AttackData,
                specialAttackData,
                AttackData.Empty,
                AttackComboCycle.Empty,
                new AttackComboCount(0),
                new TickCount(0),
                AttackKind.Normal,
                abilities,
                new CharacterUnitSummoningAction(TickCount.Empty),
                UnitActionState.Move,
                marchingLane,
                pos,
                pos,
                AlwaysCommonConditionModel.Instance,
                EmptyCommonConditionModel.Instance,
                EmptyCommonConditionModel.Instance,
                MoveLoopCount.Empty,
                locateKomaModel,
                KomaModel.Empty,
                KomaModel.Empty,
                currentTickCount,
                currentTickCount,
                TickCount.Empty,
                stateEffects,
                TickCount.Zero,
                SummonAnimationType.None,
                speechBalloons,
                UnitTransformationModel.Empty,
                DropBattlePoint.Empty,
                false,
                MoveStoppedFlag.True,
                MoveStoppedFlag.True,
                DefeatTargetFlag.False,
                OutpostDamageInvalidationFlag.False,
                PhantomizedFlag.False,
                UnitAuraType.Default,
                UnitDeathType.Normal,
                InGameScore.Empty);
        }

        TickCount GetSummoningRemainingTime(SummonAnimationType summonAnimationType, bool isBoss)
        {
            // ノックバック(InGamePresenter.PlayBossAppearanceAnimation)と
            // アニメーション(InGameView.PlaySummonAnimation)を合わせるためにBossのときはTickCount.zeroにする
            if (isBoss) return TickCount.Zero;

            return summonAnimationType switch
            {
                SummonAnimationType.Fall0 => new TickCount(70), //AnimationPrefabとは非同期。雰囲気で入れてる
                SummonAnimationType.Fall => new TickCount(70), //AnimationPrefabとは非同期。雰囲気で入れてる
                SummonAnimationType.Fall4 => new TickCount(70), //AnimationPrefabとは非同期。雰囲気で入れてる
                _ => TickCount.Zero
            };
        }

        DefeatTargetFlag IsDefeatTarget(MasterDataId mstEnemyCharacterId)
        {
            var isDefeatTarget = InGameScene.BattleEndModel.Conditions
                .OfType<DefeatUnitBattleEndConditionModel>()
                .Any(model => model.CharacterId == mstEnemyCharacterId);

            return isDefeatTarget ? DefeatTargetFlag.True : DefeatTargetFlag.False;
        }
    }
}
