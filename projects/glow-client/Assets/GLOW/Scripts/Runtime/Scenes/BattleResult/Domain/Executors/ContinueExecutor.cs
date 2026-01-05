using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Evaluator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Scenes.InGame.Domain.Battle.InGameInitializers;
using GLOW.Scenes.InGame.Domain.Battle.Logger;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Executors
{
    public class ContinueExecutor : IContinueExecutor
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IInGameLogger InGameLogger { get; }
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IInitialEnemySummonInitializer InitialEnemySummonInitializer { get; }
        [Inject] IEnemyAutoPlayerInitializer EnemyAutoPlayerInitializer { get; }
        [Inject] IInGameGimmickObjectInitializer InGameGimmickObjectInitializer { get; }
        [Inject] IBackgroundMusicPlayable BackgroundMusicPlayable { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }

        public void Execute(MasterDataId selectedStageId)
        {
            ResetInGameLogger();
            MaximizeBattlePoint();
            RecoveryPlayerOutpost();
            RecoveryEnemyOutpost();
            ResetKomaEffect();
            ResetStageTime();
            ResetRushAndMaximizeCharge();
            ResetPlayerDeckUnit(); // バトルポイントの最大化処理後に行う必要がある
            KillUnits();
            RemoveSpecialUnits();
            ResetSpecialUnitSummonInfo();
            RecoveryDefenseTarget();
            RemoveGimmickObjects();
            ResetPlacedItems();
            ResetIsContinueSelecting();

            // インゲームのコンティニュー処理
            InGameScene.IsBattleOver = BattleOverFlag.False;
            InGameScene.IsContinued = true;

            BattlePresenter.OnUpdateFieldObjects(
                InGameScene.PlayerOutpost,
                InGameScene.EnemyOutpost,
                InGameScene.CharacterUnits,
                InGameScene.SpecialUnits,
                InGameScene.DefenseTarget,
                new List<AppliedAttackResultModel>());

            // 敵ユニット死亡が条件のSequenceなどが発動してしまうため、
            // 一度OnUpdateFieldObjectsで既存敵味方ユニットなどのView側を排除後にリストを初期化し敵初期配置を行う
            ResetInGameScene();

            MstStageModel mstStage = MstStageDataRepository.GetMstStage(selectedStageId);
            ResetAutoPlayer(mstStage);
            ResetBGM(mstStage);
        }

        void MaximizeBattlePoint()
        {
            BattlePointModel battlePointModel = InGameScene.BattlePointModel;

            // 既に最大レベルかチェック
            var maxBattlePointConfig = MstConfigRepository.GetConfig(MstConfigKey.InGameMaxBattlePoint);
            var defaultMaxBattlePoint = maxBattlePointConfig.Value.ToBattlePoint();

            var chargeAmountConfig = MstConfigRepository.GetConfig(MstConfigKey.InGameBattlePointChargeAmount);
            var defaultBattlePointChargeAmount = chargeAmountConfig.Value.ToBattlePoint();

            var chargeIntervalConfig = MstConfigRepository.GetConfig(MstConfigKey.InGameBattlePointChargeInterval);
            var defaultBattlePointChargeInterval = chargeIntervalConfig.Value.ToTickCount();

            var outpostEnhanceChargeAmountOffset =
                InGameScene.OutpostEnhancement.GetEnhancementValue(OutpostEnhancementType.LeaderPointSpeed);

            // ゲート強化分のチャージ量を加算
            var chargeAmount = defaultBattlePointChargeAmount + outpostEnhanceChargeAmountOffset.ToBattlePoint();

            var outpostEnhanceMaxBattlePointOffset =
                InGameScene.OutpostEnhancement.GetEnhancementValue(OutpostEnhancementType.LeaderPointLimit);

            // ゲート強化分の最大ポイントを加算
            var maxBattlePoint =  defaultMaxBattlePoint + outpostEnhanceMaxBattlePointOffset.ToBattlePoint();

            // 最大ポイントにする
            var updatedBattlePointModel = new BattlePointModel(
                maxBattlePoint,
                maxBattlePoint,
                chargeAmount,
                defaultBattlePointChargeInterval,
                battlePointModel.RemainingTickCountForCharge,
                true);

            InGameScene.BattlePointModel = updatedBattlePointModel;

            BattlePresenter.OnUpdateDeck(
                InGameScene.DeckUnits,
                InGameScene.BattlePointModel.CurrentBattlePoint);
        }

        void RecoveryPlayerOutpost()
        {
            var playerOutpost = InGameScene.PlayerOutpost;

            InGameScene.PlayerOutpost = playerOutpost with { Hp = playerOutpost.MaxHp };
        }

        void RecoveryEnemyOutpost()
        {
            var enemyOutpost = InGameScene.EnemyOutpost;

            InGameScene.EnemyOutpost = enemyOutpost with { Hp = enemyOutpost.MaxHp };
        }

        /// <summary> 更新が入ったコマを初期状態に戻す </summary>
        void ResetKomaEffect()
        {
            InGameScene.KomaDictionary = InGameScene.KomaDictionary
                .ToDictionary(pair => pair.Key, pair =>
                {
                    var komaModel = pair.Value;

                    if (komaModel.IsEmpty()) return komaModel;

                    return komaModel with
                    {
                        KomaEffects = komaModel.KomaEffects
                            .Select(effect => effect.GetResetModel())
                            .ToList()
                    };
                });

            BattlePresenter.OnResetKomas(InGameScene.KomaDictionary);
        }

        void ResetStageTime()
        {
            InGameScene.StageTimeModel = InGameScene.StageTimeModel with
            {
                CurrentTickCount = TickCount.Zero,
                RemainingTime = InGameScene.StageTimeModel.StageTimeLimit,
            };

            BattlePresenter.OnUpdateTimeLimit(InGameScene.StageTimeModel);
        }

        void ResetRushAndMaximizeCharge()
        {
            var rushModel = InGameScene.RushModel;

            InGameScene.RushModel = rushModel with
            {
                ChargeCount = rushModel.MaxChargeCount,
                RemainingChargeTime = rushModel.ChargeTime,
                CanExecuteRushFlag = CanExecuteRushFlag.False,
                ExecuteRushFlag = ExecuteRushFlag.False,
                PowerUpStateEffectBonus = RushPowerUpStateEffectBonus.Zero
            };
        }

        /// <summary>
        /// プレイヤーキャラの召喚・必殺技クールタイム、召喚中・必殺技使用可能フラグをリセットする
        /// ※ バトルポイントの最大化処理後に行う必要がある
        /// </summary>
        void ResetPlayerDeckUnit()
        {
            InGameScene.DeckUnits = InGameScene.DeckUnits
                .Select(unit => unit with
                {
                    RemainingSummonCoolTime = TickCount.Zero,
                    IsSummoned = false,
                    RemainingSpecialAttackCoolTime = unit.SpecialAttackInitialCoolTime,
                    IsSpecialAttackReady = false,
                })
                .ToList();

            BattlePresenter.OnUpdateDeck(InGameScene.DeckUnits, InGameScene.BattlePointModel.CurrentBattlePoint);
        }

        void KillUnits()
        {
            InGameScene.CharacterUnits = InGameScene.CharacterUnits
                .Select(unit => unit with { Hp = HP.Zero })
                .ToList();
        }

        void RemoveSpecialUnits()
        {
            InGameScene.SpecialUnits = InGameScene.SpecialUnits
                .Select(unit => unit with
                {
                    RemainingLeavingTime = TickCount.Empty,
                    RemainingTimeUntilSpecialAttack = TickCount.Empty
                })
                .ToList();
        }

        void ResetSpecialUnitSummonInfo()
        {
            InGameScene.SpecialUnitSummonInfoModel = InGameScene.SpecialUnitSummonInfoModel with
            {
                IsSummonPositionSelecting = SpecialUnitSummonPositionSelectingFlag.False
            };
        }

        void RecoveryDefenseTarget()
        {
            var defenseTarget = InGameScene.DefenseTarget;
            InGameScene.DefenseTarget = defenseTarget with
            {
                Hp = defenseTarget.MaxHp
            };
        }

        void RemoveGimmickObjects()
        {
            // 現状残っているギミックオブジェクトを一旦全て削除
            BattlePresenter.OnGimmickObjectsRemoved(InGameScene.GimmickObjects);
        }
        
        void ResetPlacedItems()
        {
            // 配置済みアイテムを全て削除
            BattlePresenter.OnRemovePlacedItems(InGameScene.PlacedItems);
        }

        void ResetIsContinueSelecting()
        {
            InGameScene.IsContinueSelecting = InGameContinueSelectingFlag.False;
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;
        }

        void ResetInGameScene()
        {
            InGameScene.CharacterUnits = new List<CharacterUnitModel>();
            InGameScene.SpecialUnits = new List<SpecialUnitModel>();
            InGameScene.GimmickObjects = new List<InGameGimmickObjectModel>();
            InGameScene.DeadUnits = new List<CharacterUnitModel>();
            InGameScene.Attacks = new List<IAttackModel>();
            InGameScene.DeckUnitSummonQueue = DeckUnitSummonQueueModel.Empty;
            InGameScene.BossSummonQueue = BossSummonQueueModel.Empty;
            InGameScene.UnitSummonQueue = UnitSummonQueueModel.Empty;
            InGameScene.SpecialUnitSummonQueue = SpecialUnitSummonQueueModel.Empty;
            InGameScene.GimmickObjectToEnemyTransformationModels = new List<GimmickObjectToEnemyTransformationModel>();
            InGameScene.DefeatEnemyCount = DefeatEnemyCount.Zero;
            InGameScene.PlacedItems = new List<PlacedItemModel>();
            InGameScene.ScoreModel = InGameScene.ScoreModel with
            {
                ScoreDictionary = new Dictionary<InGameScoreType, InGameScore>()
            };
            InGameScene.BossAppearancePause = BossAppearancePauseModel.Empty;

            // 開始時の原画演出は再生しないためStart以外を初期化する
            InGameScene.MangaAnimations = InGameScene.MangaAnimations
                .Select(animation =>
                {
                    if (animation.ConditionType == MangaAnimationConditionType.Start)
                    {
                        return animation;
                    }

                    return animation with
                    {
                        IsActivated = ActivatedMangaAnimationFlag.False,
                        RemainingAnimationStartDelay = animation.AnimationStartDelay,
                    };
                }).ToList();
        }

        void ResetInGameLogger()
        {
            InGameLogger.Initialize();
        }

        void ResetAutoPlayer(MstStageModel mstStage)
        {
            // 敵AIの初期化(PvpOpponentDeckUnitsを渡しているが、Pvpはコンティニューされないためここは空のリストが渡される想定)
            var enemySequenceModel = EnemyAutoPlayerInitializer.Initialize(
                mstStage.MstAutoPlayerSequenceSetId,
                InGameScene.MstPage,
                mstStage,
                InGameScene.PvpOpponentDeckUnits);

            ResetInitialEnemyUnits(mstStage, enemySequenceModel);
            ResetGimmickObjects(enemySequenceModel);
        }

        void ResetInitialEnemyUnits(MstStageModel mstStage, MstAutoPlayerSequenceModel enemySequenceModel)
        {
            // 初期配置敵ユニットの生成
            var initialEnemySummonResult = InitialEnemySummonInitializer.InitializeEnemySummon(
                enemySequenceModel,
                InGameScene.KomaDictionary,
                InGameScene.MstPage,
                mstStage);

            // 初期配置の敵ユニットを遭遇情報として登録
            var initialEnemyCharacterIds = initialEnemySummonResult.InitialEnemyUnits
                .Select(unit => unit.CharacterId)
                .ToList();
            InGameLogger.AddDiscoverEnemyIds(initialEnemyCharacterIds);

            // 既存ユニットは削除したため、ここで初期配置敵ユニットはそのまま代入して更新する
            InGameScene.CharacterUnits = initialEnemySummonResult.InitialEnemyUnits;

            foreach (var characterUnit in initialEnemySummonResult.InitialEnemyUnits)
            {
                BattlePresenter.OnSummonCharacterWithoutEffect(characterUnit);
            }
        }

        void ResetGimmickObjects(MstAutoPlayerSequenceModel enemySequenceModel)
        {
            // ギミックオブジェクトを初期配置状態に戻すため生成
            var gimmickObjectModels =
                InGameGimmickObjectInitializer.Initialize(
                    enemySequenceModel,
                    InGameScene.KomaDictionary,
                    InGameScene.MstPage);
            InGameScene.GimmickObjects = gimmickObjectModels;

            foreach (var gimmick in gimmickObjectModels)
            {
                BattlePresenter.OnGenerateGimmickObject(gimmick);
            }
        }

        void ResetBGM(MstStageModel mstStage)
        {
            // ボス登場などでBGMが変わっていた場合用にStageのBGMを呼び出す
            BackgroundMusicPlayable.Play(mstStage.BGMAssetKey.Value);
        }
    }
}
