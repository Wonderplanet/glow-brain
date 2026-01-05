using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.TimeMeasurement;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Battle.InGameInitializers;
using GLOW.Scenes.InGame.Domain.Battle.Logger;
using GLOW.Scenes.InGame.Domain.Battle.MarchingLane;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

#if GLOW_INGAME_DEBUG
using GLOW.Debugs.InGame.Domain.Battle.InGameInitializers;
using GLOW.Debugs.InGame.Domain.Definitions;
#endif // GLOW_INGAME_DEBUG

namespace GLOW.Scenes.InGame.Domain.UseCases
{
    public class InitializeInGameUseCase
    {
        [Inject] IInGameSettingRepository InGameSettingRepository { get; }
        [Inject] IMstPageDataRepository MstPageDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IKomaModelFactory KomaModelFactory { get; }
        [Inject] IMangaAnimationModelFactory MangaAnimationModelFactory { get; }
        [Inject] IMstMangaAnimationDataRepository MstMangaAnimationDataRepository { get; }
        [Inject] IScoreCalculateModelFactory _scoreCalculateModelFactory { get; }
        [Inject] IMarchingLaneDirector MarchingLaneDirector { get; }
        [Inject] IInGamePreferenceInitializer InGamePreferenceInitializer { get; }
        [Inject] IEnemyAutoPlayerInitializer EnemyAutoPlayerInitializer { get; }
        [Inject] IPlayerAutoPlayerInitializer PlayerAutoPlayerInitializer { get; }
        [Inject] IInitialEnemySummonInitializer InitialEnemySummonInitializer { get; }
        [Inject] IDefenseTargetInitializer DefenseTargetInitializer { get; }
        [Inject] IInGameGimmickObjectInitializer InGameGimmickObjectInitializer { get; }
        [Inject] ISpecialAttackCutInLogInitializer SpecialAttackCutInLogInitializer { get; }
        [Inject] IRushInitializer RushInitializer { get; }
        [Inject] IScoreInitializer ScoreInitializer { get; }
        [Inject] IBattleSpeedInitializer BattleSpeedInitializer { get; }
        [Inject] IOutpostEnhancementInitializer OutpostEnhancementInitializer { get; }
        [Inject] IStageQuestInitializer StageQuestInitializer { get; }
        [Inject] IBattleEndConditionInitializer BattleEndConditionInitializer { get; }
        [Inject] IOutpostInitializer OutpostInitializer { get; }
        [Inject] IDeckInitializer DeckInitializer { get; }
        [Inject] IBattlePointInitializer BattlePointInitializer { get; }
        [Inject] IArtworkBonusHpInitializer ArtworkBonusHpInitializer { get; }
        [Inject] IInGameLoadingMeasurement InGameLoadingMeasurement { get; }
        [Inject] IInGameLogger InGameLogger { get; }

#if GLOW_INGAME_DEBUG
        [Inject] IInGameDebugInitializer InGameDebugInitializer { get; }
        [Inject] IInGameDebugSettingRepository DebugSettingRepository { get; }
#endif // GLOW_INGAME_DEBUG

        public InitializeResultModel Initialize()
        {
            MarchingLaneDirector.Initialize();

            InGameScene.InGameSetting = InGameSettingRepository.GetInGameSetting();
            InGameScene.BossAppearancePause = BossAppearancePauseModel.Empty;

            // InGamePreference関係の初期化
            var preferenceInitializationResult = InGamePreferenceInitializer.Initialize();
            var isContinueSelecting = preferenceInitializationResult.IsContinueSelecting;
            InGameScene.IsContinueSelecting = isContinueSelecting;

            // ステージとクエストデータ取得
            var stageQuestInitializationResult = StageQuestInitializer.Initialize();
            var mstStage = stageQuestInitializationResult.MstStage;
            var mstAdventBattle = stageQuestInitializationResult.MstAdventBattle;
            var selectedStage = stageQuestInitializationResult.SelectedStageModel;
            var mstInGameModel = stageQuestInitializationResult.MstInGameModel;
            var mstQuest = stageQuestInitializationResult.MstQuestModel;
            var mstInGameSpecialRuleModels = stageQuestInitializationResult.MstInGameSpecialRules;
            var mstInGameSpecialRuleUnitStatusModels = stageQuestInitializationResult.MstInGameSpecialRuleUnitStatusModels;
            var mstStageEndConditions = stageQuestInitializationResult.MstStageEndConditions;
            InGameScene.MstInGame = mstInGameModel;
            InGameScene.EventBonusGroupId = mstInGameModel.EventBonusGroupId;
            InGameScene.Type = selectedStage.InGameType;
            InGameScene.MstQuest = mstQuest;
            InGameScene.SpecialRuleUnitStatusModels = mstInGameSpecialRuleUnitStatusModels;

            // 中断復帰の処理
            Resume(isContinueSelecting, InGameScene.Type);

            // UserPropertyを取得
            var userProperty = UserPropertyRepository.Get();

            // ユーザーが購入したパスでバトルスピード変更が可能かどうか
            var battleSpeedInitializationResult = BattleSpeedInitializer.Initialize(preferenceInitializationResult.BattleSpeed);
            InGameScene.CurrentBattleSpeed = battleSpeedInitializationResult.CurrentBattleSpeed;
            InGameScene.BattleSpeedList = battleSpeedInitializationResult.BattleSpeedList;

            // 原画データ取得
            var artworkBonusHpInitializationResult = ArtworkBonusHpInitializer.Initialize();
            var artworkBonusHp = artworkBonusHpInitializationResult.PlayerArtworkBonusHp;
            var pvpOpponentArtworkBonusHp = artworkBonusHpInitializationResult.PvpOpponentArtworkBonusHp;
            InGameScene.ArtworkBonusHp = artworkBonusHp;
            InGameScene.PvpOpponentArtworkBonusHp = pvpOpponentArtworkBonusHp;


            // ゲート強化データ取得
            var outpostEnhancementInitializerResult =
                OutpostEnhancementInitializer.Initialize(selectedStage.InGameType);
            var outpostEnhancement = outpostEnhancementInitializerResult.OutpostEnhancement;
            var pvpOpponentOutpostEnhancement = outpostEnhancementInitializerResult.PvpOpponentOutpostEnhancement;
            InGameScene.OutpostEnhancement = outpostEnhancement;
            InGameScene.PvpOpponentOutpostEnhancement = pvpOpponentOutpostEnhancement;

            // コマページデータ取得
            MstPageModel mstPage = MstPageDataRepository.GetPage(mstInGameModel.MstPageId);
            InGameScene.MstPage = mstPage;

            // 終了条件の初期化
            var battleEndModel = BattleEndConditionInitializer.Initialize(
                mstStageEndConditions,
                mstInGameSpecialRuleModels,
                selectedStage.InGameType,
                mstQuest,
                mstInGameModel.MstDefenseTargetId);
            InGameScene.BattleEndModel = battleEndModel;

            // コマ初期化
            var komaDictionary = InitializeKomaDictionary(mstPage);

            // 座標系の初期化
            var fieldToPlayerOutpostMatrix = InGameConstants.FieldToPlayerOutpostMatrix;

            var fieldToEnemyOutpostMatrix =
                Matrix3x3.Translate(-(InGameScene.MstPage.TotalWidth - 0.15f), -0.05f) * Matrix3x3.Scale(-1f, 1f);

            CoordinateConverter.SetTransformationMatrix(fieldToPlayerOutpostMatrix, fieldToEnemyOutpostMatrix);
            CoordinateConverter.SetPage(mstPage.PageWidth, mstPage.KomaLineHeightList);

            // 経過時間、制限時間の初期化
            var timeLimitModel = InitializeTimeLimit(mstStageEndConditions, mstInGameSpecialRuleModels);

            // デッキの初期化
            var deckInitializerResult = DeckInitializer.Initialize(outpostEnhancement, pvpOpponentOutpostEnhancement);
            var deckUnits = deckInitializerResult.DeckUnits;
            var pvpOpponentDeckUnits = deckInitializerResult.PvpOpponentDeckUnits;
            InGameScene.DeckUnits = deckUnits;
            InGameScene.PvpOpponentDeckUnits = pvpOpponentDeckUnits;

            // スペシャルユニット設定の初期化
            InGameScene.SpecialUnitSummonInfoModel = InitializeSpecialUnitSummonModel(mstPage);

            // 拠点の初期化
            // 味方ゲートHPの特別ルールが設定されている場合はHPを上書きする
            var outpostInitializerResult = OutpostInitializer.Initialize(
                selectedStage.InGameType,
                mstQuest.QuestType,
                mstInGameModel.PlayerOutpostAssetKey,
                mstInGameModel.MstEnemyOutpostId,
                mstInGameSpecialRuleModels,
                outpostEnhancement,
                pvpOpponentOutpostEnhancement,
                artworkBonusHp,
                pvpOpponentArtworkBonusHp,
                isContinueSelecting);
            var playerOutpost = outpostInitializerResult.PlayerOutpost;
            var enemyOutpost = outpostInitializerResult.EnemyOutpost;
            InGameScene.PlayerOutpost = playerOutpost;
            InGameScene.EnemyOutpost = enemyOutpost;

            // 総攻撃の初期化
            var rushInitializerResult = RushInitializer.Initialize(
                InGameScene.MstQuest.QuestType,
                deckUnits,
                pvpOpponentDeckUnits,
                outpostEnhancement,
                pvpOpponentOutpostEnhancement);

            var rushModel = rushInitializerResult.RushModel;
            var pvpOpponentRushModel = rushInitializerResult.PvpOpponentRushModel;
            InGameScene.RushModel = rushModel;
            InGameScene.PvpOpponentRushModel = pvpOpponentRushModel;

            // バトルポイントの初期化
            var battlePointInitializerResult = BattlePointInitializer.Initialize(
                selectedStage.InGameType,
                selectedStage.SelectedMstAdventBattleId,
                selectedStage.SelectedSysPvpSeasonId,
                outpostEnhancement,
                pvpOpponentOutpostEnhancement,
                isContinueSelecting);
            var battlePointModel = battlePointInitializerResult.BattlePointModel;
            var pvpOpponentBattlePointModel = battlePointInitializerResult.PvpOpponentBattlePointModel;
            InGameScene.BattlePointModel = battlePointModel;
            InGameScene.PvpOpponentBattlePointModel = pvpOpponentBattlePointModel;

            // 敵を倒した数のログの初期化
            InGameLogger.Initialize();

            // スコアの初期化
            // TODO:PVP:ScoreInitializerにこのあたりも含める。
            var scoreAdditionModel =  new AdventBattleScoreAdditionModel(
                mstAdventBattle.ScoreAdditionType,
                mstAdventBattle.DamageScoreAdditionCoef);

            InGameScene.ScoreCalculateModel = _scoreCalculateModelFactory.Create(
                selectedStage.InGameType,
                mstQuest.QuestType,
                scoreAdditionModel);

            InGameScene.ScoreModel = ScoreInitializer.InitializeScore(mstQuest.QuestType);

            // 敵AIの初期化
            var enemySequenceModel = EnemyAutoPlayerInitializer.Initialize(
                mstInGameModel.MstAutoPlayerSequenceSetId,
                mstPage,
                mstInGameModel,
                pvpOpponentDeckUnits);

            // プレイヤーAIの初期化
            PlayerAutoPlayerInitializer.Initialize(deckUnits);

            // 初期配置キャラ
            var initialCharacterUnits = InGameScene.CharacterUnits.ToList();
            var enemySummonInitializerResult = InitialEnemySummonInitializer.InitializeEnemySummon(
                enemySequenceModel,
                komaDictionary,
                mstPage,
                mstInGameModel);
            initialCharacterUnits.AddRange(enemySummonInitializerResult.InitialEnemyUnits);

            InGameScene.CharacterUnits = InGameScene.CharacterUnits
                .Concat(enemySummonInitializerResult.InitialEnemyUnits)
                .ToList();

            // 初期配置の敵ユニットを遭遇情報として登録
            var initialEnemyCharacterIds = enemySummonInitializerResult.InitialEnemyUnits
                .Select(unit => unit.CharacterId)
                .ToList();
            InGameLogger.AddDiscoverEnemyIds(initialEnemyCharacterIds);

            // ギミックオブジェクト
            var gimmickObjectModels =
                InGameGimmickObjectInitializer.Initialize(enemySequenceModel, komaDictionary, mstPage);
            InGameScene.GimmickObjects = gimmickObjectModels;

            // 防衛オブジェクト
            var defenseTargetModel = DefenseTargetInitializer.Initialize(mstInGameModel.MstDefenseTargetId);
            InGameScene.DefenseTarget = defenseTargetModel;

            // コンティニュー不可かどうかの初期化
            var noContinueRule = mstInGameSpecialRuleModels.FirstOrDefault(
                rule => rule.RuleType == RuleType.NoContinue,
                MstInGameSpecialRuleModel.Empty);

            InGameScene.IsNoContinue = noContinueRule.RuleValue.ToNoContinueFlag();

            // ステージ開始時の原画演出
            var startMangaAnimationModel = MstMangaAnimationDataRepository
                .GetMangaAnimationsByStageId(mstStage.Id)
                .FirstOrDefault(model => model.ConditionType == MangaAnimationConditionType.Start, MstMangaAnimationModel.Empty);

            // 開始時のノイズ演出をするか
            var needsBattleStartNoiseAnimation = NeedsBattleStartNoiseAnimation(mstQuest);

            // マンガ演出Modelの初期化
            var mangaAnimationModels = InitializeMangaAnimations(mstStage.Id);

            // ロードするもの
            var mangaAnimationModelList = MstMangaAnimationDataRepository.GetMangaAnimationsByStageId(mstStage.Id);

            var initialLoadAssetsModel = new InitialLoadAssetsModel(
                GetLoadUnitAssetKeys(deckUnits, pvpOpponentDeckUnits, enemySequenceModel),
                GetLoadKomaEffectAssetKeys(mstPage),
                GetLoadKomaBackgroundAssetKeys(mstPage),
                GetLoadMangaAnimationAssetKeys(mangaAnimationModelList),
                GetLoadOutpostAssetKeys(new List<OutpostModel>() { playerOutpost, enemyOutpost }),
                GetLoadGimmickObjectAssetKeys(gimmickObjectModels),
                GetLoadDefenseTargetAssetKeys(defenseTargetModel),
                GetLoadBGMAssetKeys(mstInGameModel));

            // 必殺ワザカットインログの初期化
            var specialAttackCutInLogInitializationResult = SpecialAttackCutInLogInitializer.Initialize();

            // コンティニュー時の更新処理
            if (InGameScene.IsContinued)
            {
                // リーダーPレベル最大
                battlePointModel = MaximizeBattlePoint();

                // 総攻撃ゲージ最大
                rushModel = MaximizeRushCharge(rushModel);

                // 原画演出一覧から開始時の演出を演出済みの状態にし非活性にする
                SetActivatedStartMangaAnimation(mangaAnimationModels);
                startMangaAnimationModel = MstMangaAnimationModel.Empty;

                // ノイズ演出を無しに
                needsBattleStartNoiseAnimation = BattleStartNoiseAnimationNeedFlag.False;
            }

            // NOTE: ログの計測終了・送信
            InGameLoadingMeasurement.ReportAndClear();

#if GLOW_INGAME_DEBUG
            // デバッグ機能の初期化
            InGameScene.Debug = InGameDebugInitializer.Initialize(
                mstInGameModel,
                enemySequenceModel,
                playerOutpost,
                enemyOutpost,
                pvpOpponentDeckUnits,
                selectedStage.InGameType);
#endif // GLOW_INGAME_DEBUG

            // 初期化結果を返す
            return new InitializeResultModel(
                mstInGameModel.InGameNumber,
                mstInGameModel.InGameName,
                mstInGameModel.BGMAssetKey,
                mstInGameModel.BossBGMAssetKey,
                deckUnits,
                userProperty.IsTwoRowDeck,
                mstPage,
                komaDictionary,
                playerOutpost,
                enemyOutpost,
                rushModel,
                pvpOpponentRushModel,
                initialCharacterUnits,
                gimmickObjectModels,
                defenseTargetModel,
                battlePointModel,
                battleSpeedInitializationResult.CurrentBattleSpeed,
                preferenceInitializationResult.IsAutoEnabled,
                selectedStage.InGameType,
                mstQuest.QuestType,
                startMangaAnimationModel.AssetKey,
                startMangaAnimationModel.AnimationSpeed,
                needsBattleStartNoiseAnimation,
                timeLimitModel,
                battleEndModel,
                userProperty.SpecialAttackCutInPlayType,
                userProperty.IsDamageDisplay,
                specialAttackCutInLogInitializationResult.SpecialAttackCutInPlayedUnitIds,
                initialLoadAssetsModel,
                isContinueSelecting);
        }

        /// <summary>
        /// 中断復帰の処理
        /// </summary>
        void Resume(InGameContinueSelectingFlag isContinueSelecting, InGameType currentInGameType)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var sessionModel = gameFetchOther.UserInGameStatusModel;

            // 現在のInGameTypeとセッションのInGameType(InGameContentType)からコンティニュー判定行うか判断
            bool isContinueAvailable = IsContinueAvailable(currentInGameType, sessionModel.InGameType);

            // コンティニュー判定を行う場合
            if(isContinueAvailable)
            {
                InGameScene.IsContinued = sessionModel.ContinueCount > ContinueCount.Zero;

                // コンティニュー選択中の場合
                if(isContinueSelecting)
                {
                    InGameScene.IsBattleOver = BattleOverFlag.True;
                }
            }
        }

        bool IsContinueAvailable(InGameType currentInGameType, InGameType sessionInGameType)
        {
            // sessionInGameTypeはNromal遷移時に更新され、AdventBattle・Pvp遷移時は更新されない
            // イメージ
            // 1.コンティニューできないステージ→Normal
            // ・・UserInGameStatusModel(sessionInGameType)が更新されている
            // ・・sessionInGameTypeで弾く(初回遷移なので問題なし
            // 2.その後、Normalに対して中断復帰
            // ・・ログイン時にUserInGameStatusModel(sessionInGameType)が更新されている
            // ・・currentInGameType、sessionInGameType共に一致するのでContinueCountを確認しコンティニュー判定を行う

            // 現在のコンテンツがコンティニュー可能なTypeか確認
            switch (currentInGameType)
            {
                case InGameType.Normal:
                    // 現在のコンテンツとセッションが一致する場合、コンティニュー判定を行う
                    return currentInGameType == sessionInGameType;
                case InGameType.AdventBattle:
                case InGameType.Pvp:
                    // コンティニュー不可コンテンツ、コンティニュー判定を行わない
                    return false;
                default:
                    // InGameTypeを追加時の対応漏れ対策
                    // 追加した場合、case追加しコンティニュー可否の判定すること
                    throw new System.ArgumentOutOfRangeException(nameof(currentInGameType), currentInGameType, null);
            }
        }

        SpecialUnitSummonInfoModel InitializeSpecialUnitSummonModel(MstPageModel mstPage)
        {
            var maxKomaCount = new KomaCount(mstPage.KomaList.Count);
            var komaRange = new SpecialUnitSummonKomaRange(maxKomaCount, maxKomaCount);

            // 将来的に縛りでスペシャルユニット不可のステージが必要になった際などここで召喚可フラグを制御
            return new SpecialUnitSummonInfoModel(
                CanSpecialUnitSummonFlag.True,
                komaRange,
                SpecialUnitSummonPositionSelectingFlag.False);
        }

        IReadOnlyDictionary<KomaId, KomaModel> InitializeKomaDictionary(MstPageModel mstPage)
        {
            var komaDictionary = new Dictionary<KomaId, KomaModel>();

            foreach (var mstKoma in mstPage.KomaList)
            {
                var koma = KomaModelFactory.Create(mstKoma);
                komaDictionary.Add(mstKoma.KomaId, koma);
            }

            InGameScene.KomaDictionary = komaDictionary;

            return komaDictionary;
        }

        StageTimeModel InitializeTimeLimit(
            IReadOnlyList<MstStageEndConditionModel> mstStageEndConditions,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            var timeLimitEndCondition = mstStageEndConditions
                .FirstOrDefault(mst => mst.ConditionType == StageEndConditionType.TimeOver, MstStageEndConditionModel.Empty);
            var speedAttackRule = mstInGameSpecialRuleModels
                .FirstOrDefault(mst => mst.RuleType == RuleType.SpeedAttack, MstInGameSpecialRuleModel.Empty);

            var timeLimit = TimeLimit.Empty;
            var inGameTimeRule = InGameTimeRule.None;
            if (!timeLimitEndCondition.IsEmpty())
            {
                timeLimit = timeLimitEndCondition.ConditionValue1.ToTimeLimit();
                inGameTimeRule = timeLimitEndCondition.StageEndType == StageEndType.Defeat
                    ? InGameTimeRule.TimeLimitDefeat
                    : InGameTimeRule.TimeLimitVictory;
            }
            else if (!speedAttackRule.IsEmpty())
            {
                timeLimit = TimeLimit.SpeedAttack;
                inGameTimeRule = InGameTimeRule.SpeedAttack;
            }

            var inGameTimeLimit = InGameTimeLimit.FromTimeLimit(timeLimit);

            var stageTimeModel = new StageTimeModel(
                inGameTimeRule,
                TickCount.Zero,
                inGameTimeLimit,
                inGameTimeLimit,
                RemainingTimeTextColor.GetColor(inGameTimeLimit.IsHighlightTextTime())
                );
            InGameScene.StageTimeModel = stageTimeModel;
            return stageTimeModel;
        }

        IReadOnlyList<MangaAnimationModel> InitializeMangaAnimations(MasterDataId mstStageId)
        {
            if (mstStageId.IsEmpty())
            {
                var emptyAnimationModels = new List<MangaAnimationModel>();
                InGameScene.MangaAnimations = emptyAnimationModels;
                return emptyAnimationModels;
            }
            var mangaAnimationModels = MstMangaAnimationDataRepository
                .GetMangaAnimationsByStageId(mstStageId)
                .Select(MangaAnimationModelFactory.Create)
                .Where(model => !model.IsEmpty())
                .ToList();

            InGameScene.MangaAnimations = mangaAnimationModels;
            return mangaAnimationModels;
        }

        BattleStartNoiseAnimationNeedFlag NeedsBattleStartNoiseAnimation(MstQuestModel mstQuest)
        {
            if (mstQuest.IsEmpty()) return BattleStartNoiseAnimationNeedFlag.False;

            return mstQuest.QuestType is QuestType.Normal
                ? BattleStartNoiseAnimationNeedFlag.True
                : BattleStartNoiseAnimationNeedFlag.False;
        }

        IReadOnlyList<KomaBackgroundAssetKey> GetLoadKomaBackgroundAssetKeys(MstPageModel pageModel)
        {
            return pageModel.KomaList
                .Select(koma => koma.BackgroundAssetKey)
                .Distinct()
                .ToList();
        }

        IReadOnlyList<KomaEffectAssetKey> GetLoadKomaEffectAssetKeys(MstPageModel pageModel)
        {
            return pageModel.KomaList
                .Where(koma => koma.KomaEffectType != KomaEffectType.None)
                .Select(koma => koma.KomaEffectType)
                .Distinct()
                .Select(KomaEffectAssetKey.FromKomaEffectType)
                .ToList();
        }

        IReadOnlyList<UnitAssetKey> GetLoadUnitAssetKeys(
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            MstAutoPlayerSequenceModel enemySequenceModel)
        {
            var deckUnitListWithMstCharacterModel = deckUnits.Concat(pvpOpponentDeckUnits)
                .Where(deckUnit => !deckUnit.IsEmptyUnit())
                .Select(deckUnit => new
                {
                    DeckUnit = deckUnit,
                    MstCharacterModel = MstCharacterDataRepository.GetCharacter(deckUnit.CharacterId)
                })
                .ToList();

            // 通常召喚 & ギミックオブジェクトから敵への変換による召喚
            var summoningEnemyStageParameterModelList = enemySequenceModel.EnemySummonElements
                .Concat(enemySequenceModel.TransformGimmickObjectToEnemyElements)
                .Select(element => MstEnemyCharacterDataRepository.GetEnemyStageParameter(element.Action.Value.ToMasterDataId()))
                .ToList();

            // 変身敵のデータを取得
            var enemyStageParameterModelList = new List<MstEnemyStageParameterModel>(summoningEnemyStageParameterModelList);

            foreach (var enemyStageParameterModel in summoningEnemyStageParameterModelList)
            {
                var transformationEnemyStageParameterModels = GetTransformationEnemyStageParameterModels(
                    enemyStageParameterModel,
                    enemyStageParameterModelList);

                enemyStageParameterModelList.AddRange(transformationEnemyStageParameterModels);
            }

            // キャラのAssetKeyリスト
            var unitAssetKeys = deckUnitListWithMstCharacterModel
                .Select(element => element.MstCharacterModel.AssetKey)
                .ToList();

            unitAssetKeys.AddRange(enemyStageParameterModelList.Select(model => model.AssetKey));

#if GLOW_DEBUG
            if (DebugSettingRepository.Get().IsOverrideSummons)
            {
                unitAssetKeys.AddRange(DebugSettingRepository.Get().OverrideUnitAssetKeys);
            }
#endif

            return unitAssetKeys
                .Where(key => !key.IsEmpty())
                .Distinct()
                .ToList();
        }

        List<MstEnemyStageParameterModel> GetTransformationEnemyStageParameterModels(
            MstEnemyStageParameterModel enemyStageParameterModel,
            IReadOnlyList<MstEnemyStageParameterModel> enemyStageParameterModels)
        {
            if (enemyStageParameterModel.TransformationParameter.IsEmpty())
            {
                return new List<MstEnemyStageParameterModel>();
            }

            var transformationEnemyStageParameterId =
                enemyStageParameterModel.TransformationParameter.MstEnemyStageParameterId;

            if (enemyStageParameterModels.Any(model => model.Id == transformationEnemyStageParameterId))
            {
                return new List<MstEnemyStageParameterModel>();
            }

            var transformationEnemyStageParameterModel = MstEnemyCharacterDataRepository
                .GetEnemyStageParameter(transformationEnemyStageParameterId);

            var allEnemyStageParameterModels = enemyStageParameterModels
                .ToList()
                .ChainAdd(transformationEnemyStageParameterModel);

            var nestedTransformationEnemyStageParameterModels = GetTransformationEnemyStageParameterModels(
                transformationEnemyStageParameterModel,
                allEnemyStageParameterModels);

            var list = nestedTransformationEnemyStageParameterModels;
            list.Add(transformationEnemyStageParameterModel);

            return list;
        }

        IReadOnlyList<MangaAnimationAssetKey> GetLoadMangaAnimationAssetKeys(
            IReadOnlyList<MstMangaAnimationModel> mangaAnimationModels)
        {
            return mangaAnimationModels
                .Select(model => model.AssetKey)
                .Distinct()
                .ToList();
        }

        IReadOnlyList<OutpostAssetKey> GetLoadOutpostAssetKeys(List<OutpostModel> outpostModels)
        {
            return outpostModels
                .Where(model => !model.OutpostAssetKey.IsEmpty())
                .Select(model => model.OutpostAssetKey)
                .Distinct()
                .ToList();
        }

        IReadOnlyList<InGameGimmickObjectAssetKey> GetLoadGimmickObjectAssetKeys(
            IReadOnlyList<InGameGimmickObjectModel> gimmickObjectModels)
        {
            return gimmickObjectModels
                .Where(model => !model.AssetKey.IsEmpty())
                .Select(model => model.AssetKey)
                .Distinct()
                .ToList();
        }

        IReadOnlyList<DefenseTargetAssetKey> GetLoadDefenseTargetAssetKeys(DefenseTargetModel defenseTargetModel)
        {
            return !defenseTargetModel.AssetKey.IsEmpty()
                ? new List<DefenseTargetAssetKey> { defenseTargetModel.AssetKey }
                : new List<DefenseTargetAssetKey>();
        }

        IReadOnlyList<BGMAssetKey> GetLoadBGMAssetKeys(IMstInGameModel mstInGameModel)
        {
            var bgmAssetKeys = new List<BGMAssetKey>
            {
                mstInGameModel.BGMAssetKey,
                new BGMAssetKey(BGMAssetKeyDefinitions.BGM_victory_result)
            };

            if (!mstInGameModel.BossBGMAssetKey.IsEmpty())
            {
                bgmAssetKeys.Add(mstInGameModel.BossBGMAssetKey);
            }

            return bgmAssetKeys;
        }

        #region コンティニュー後の中断復帰時の処理

        BattlePointModel MaximizeBattlePoint()
        {
            // 最大バトルポイントの取得
            var maxBattlePointConfig = MstConfigRepository.GetConfig(MstConfigKey.InGameMaxBattlePoint);
            var defaultMaxBattlePoint = maxBattlePointConfig.Value.ToBattlePoint();

            var chargeAmountConfig = MstConfigRepository.GetConfig(MstConfigKey.InGameBattlePointChargeAmount);
            var defaultBattlePointChargeAmount = chargeAmountConfig.Value.ToBattlePoint();

            var chargeIntervalConfig = MstConfigRepository.GetConfig(MstConfigKey.InGameBattlePointChargeInterval);
            var defaultBattlePointChargeInterval = chargeIntervalConfig.Value.ToTickCount();

            // 最大レベル、最大ポイントにする
            var updatedBattlePointModel = new BattlePointModel(
                defaultMaxBattlePoint,
                defaultMaxBattlePoint,
                defaultBattlePointChargeAmount,
                defaultBattlePointChargeInterval,
                new TickCount(0),
                true);

            InGameScene.BattlePointModel = updatedBattlePointModel;

            return updatedBattlePointModel;
        }

        RushModel MaximizeRushCharge(RushModel rushModel)
        {
            // 総攻撃ゲージを最大に
            var updatedRushModel = rushModel with
            {
                ChargeCount = rushModel.MaxChargeCount,
                RemainingChargeTime = TickCount.Zero,
                CanExecuteRushFlag = CanExecuteRushFlag.False,
                ExecuteRushFlag = ExecuteRushFlag.False,
                PowerUpStateEffectBonus = RushPowerUpStateEffectBonus.Zero
            };

            InGameScene.RushModel = updatedRushModel;
            return updatedRushModel;
        }

        IReadOnlyList<MangaAnimationModel> SetActivatedStartMangaAnimation(
            IReadOnlyList<MangaAnimationModel> mangaAnimationModels)
        {
            // 原画演出から開始時の演出を演出済みの状態にし非活性にする
            var updatedMangaAnimationModels =  mangaAnimationModels
                .Select(animation =>
                {
                    if (animation.ConditionType != MangaAnimationConditionType.Start)
                    {
                        return animation;
                    }

                    return animation with
                    {
                        IsActivated = ActivatedMangaAnimationFlag.True,
                        RemainingAnimationStartDelay = TickCount.Zero,
                    };
                }).ToList();

            InGameScene.MangaAnimations = updatedMangaAnimationModels;
            return updatedMangaAnimationModels;
        }

        #endregion
    }
}
