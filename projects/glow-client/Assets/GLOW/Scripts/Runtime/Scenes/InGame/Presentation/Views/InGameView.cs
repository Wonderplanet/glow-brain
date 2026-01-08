using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Modules.TimeScaleController;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Debugs.InGame.Presentation.Views.Components;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Presentation.Manager;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Common;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Components.MangaAnimation;
using GLOW.Scenes.InGame.Presentation.Components.Rush;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.TimelineTracks;
using GLOW.Scenes.InGame.Presentation.UI.UIEffect;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using UnityEngine.UI;
using WonderPlanet.PerformanceProfiler;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views
{
    public class InGameView : UIView, IScreenFlashTrackClipDelegate
    {
        static readonly Vector3 WindowToIconDiffPos = new (0f, 80f);

        [SerializeField] InGameUIBackground _background;
        [SerializeField] UIText _stageNumberText;
        [SerializeField] UIText _stageNameText;
        [SerializeField] RectTransform _footerRectTransform;
        [SerializeField] VerticalLayoutGroup _pageLayoutGroup;
        [SerializeField] PageComponent _pageComponent;
        [SerializeField] InGameFooterComponent _footerComponent;
        [SerializeField] UIObject _optionButton;
        [SerializeField] BattleSpeedButton _battleSpeedButton;
        [SerializeField] UIToggleableComponent _autoButton;
        [SerializeField] UIObject _settingButtonObj;
        [SerializeField] MangaAnimationPlayer _mangaAnimationPlayer;
        [SerializeField] CutInPlayer _cutInPlayer;
        [SerializeField] RushPlayer _rushPlayer;
        [SerializeField] UIObject _defeatUIObject;

        [SerializeField] UIObject _tapGuardObject;
        [SerializeField] UIObject _pageTapGuardObject;
        [SerializeField] UIObject _headerTapGuardObject;

        [SerializeField] UIObject _stageNumberRootObject;
        [SerializeField] Canvas _frontViewCanvas;
        [SerializeField] GameObject _noFrontViewObject;

        [SerializeField] InGameBlackCurtainComponent _blackCurtainComponent;
        [SerializeField] InGameBlackCurtainComponent _pageWithoutBlackCurtainComponent;
        [SerializeField] InGameFlashComponent _flashComponent;
        [SerializeField] TapToSkipComponent _tapToSkipComponent;
        [SerializeField] UIObject _tapToStartBattle;
        [SerializeField] InGameDebugInfoComponent _debugInfoComponent;
        [SerializeField] float _battleStartScrollDurationFor100Pixels = 0.5f;
        [SerializeField] BattleStartNoiseComponent _battleStartNoiseComponent;
        [SerializeField] SpeechBalloonComponent _speechBalloonPrefab;
        [SerializeField] InGameEventComponent _eventComponent;
        [SerializeField] InGameTimeCountDownComponent _timeCountDownComponent;
        [SerializeField] TutorialIntroductionMangaManager _tutorialIntroductionMangaManager;
        [SerializeField] Button _rushSkipButton;
        [SerializeField] UIText _rushSkipText;


        [Inject] BattleFieldView BattleFieldView { get; }
        [Inject] BattleSummonEffectManager BattleSummonEffectManager { get; }
        [Inject] IViewCoordinateConverter ViewCoordinateConverter { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] PrefabFactory<KomaSetComponent> KomaSetComponentFactory { get; }
        [Inject] PrefabFactory<MangaAnimation> MangaAnimationFactory { get; }
        [Inject] IKomaBackgroundContainer KomaBackgroundContainer { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] IMangaAnimationContainer MangaAnimationContainer { get; }
        [Inject] ITimeScaleController TimeScaleController { get; }
        [Inject] UIEffectManager UIEffectManager { get; }

        FieldViewConstructData _fieldViewConstructData;
        Action<PageCoordV2> _pageTouchCallback;

        public Action<MasterDataId> OnSummonButtonTapped { get; set; }
        public Action<MasterDataId> OnUseSpecialAttackButtonTapped { get; set; }
        public Action<MasterDataId> OnSpecialUnitSummonButtonTapped { get; set; }
        public Action<UserDataId> OnUnitDetailLongPressed { get; set; }
        public Action OnApplicationFocusAction { get; set; }
        public FieldViewConstructData FieldViewConstructData => _fieldViewConstructData;
        public float BattleStartScrollDurationFor100Pixels => _battleStartScrollDurationFor100Pixels;
        public TutorialIntroductionMangaManager TutorialIntroductionMangaManager => _tutorialIntroductionMangaManager;

        public bool IsDeadAnimation
        {
            get => BattleFieldView.IsDeadAnimation;
            set => BattleFieldView.IsDeadAnimation = value;
        }

        public bool DebugInfoHidden
        {
            get => _debugInfoComponent.Hidden;
            set => _debugInfoComponent.Hidden = value;
        }

        public bool IsScrollablePage => _pageComponent.IsScrollablePage;


#if GLOW_INGAME_DEBUG
        void Update()
        {
            UpdateDebugInfo();
        }
#endif
        void OnApplicationFocus(bool hasFocus)
        {
            if (!hasFocus)
            {
                OnApplicationFocusAction?.Invoke();
            }
            else
            {
            }
        }

        public async UniTask InitializeInGame(InitializeViewModel initializeViewModel, CancellationToken cancellationToken)
        {
            _stageNameText.SetText(initializeViewModel.InGameName.Value);

            InitializeStageNumber(initializeViewModel);
            await InitializeFieldView(initializeViewModel, cancellationToken);
            InitializePageComponent(initializeViewModel);
            InitializeFieldUnit(initializeViewModel);
            InitializeGimmickObject(initializeViewModel);
            InitializeDefenseTargetObject(initializeViewModel);
            InitializeBattleSpeedButton(initializeViewModel);
            InitializeEventComponent(initializeViewModel);
            InitializeCutInPlayer();
            InitializeMangaAnimationPlayer();
            InitializeRushPlayer();

            SwitchAutoButton(initializeViewModel.IsAutoEnabled);

            _footerComponent.Initialize(initializeViewModel, UIEffectManager);

            _footerComponent.OnSummonButtonTapped =
                characterId => OnSummonButtonTapped?.Invoke(characterId);

            _footerComponent.OnUseSpecialAttackButtonTapped =
                characterId => OnUseSpecialAttackButtonTapped?.Invoke(characterId);

            _footerComponent.OnSpecialUnitSummonButtonTapped =
                characterId => OnSpecialUnitSummonButtonTapped?.Invoke(characterId);

            _footerComponent.OnButtonLongPressed =
                characterId => OnUnitDetailLongPressed?.Invoke(characterId);
        }

        public void DisableRushAnimSkip()
        {
            _rushSkipButton.interactable = false;
            _rushSkipText.Hidden = true;
        }

        void InitializeStageNumber(InitializeViewModel initializeViewModel)
        {
            if (initializeViewModel.StageNumberCaption.IsEmpty())
            {
                _stageNumberRootObject.Hidden = true;
                return;
            }

            _stageNumberRootObject.Hidden = false;
            _stageNumberText.SetText(initializeViewModel.StageNumberCaption.Value);
        }

        void InitializePageComponent(InitializeViewModel initializeViewModel)
        {
            _pageComponent.InitializeBattlePage(
                initializeViewModel.MstPageModel,
                BattleFieldView,
                ViewCoordinateConverter,
                CoordinateConverter,
                KomaSetComponentFactory,
                KomaBackgroundContainer);

            _pageComponent.OnTouch = OnPageTouchInternal;
        }

        async UniTask InitializeFieldView(InitializeViewModel initializeViewModel, CancellationToken cancellationToken)
        {
            _fieldViewConstructData = new FieldViewConstructDataBuilder()
                .SetPageModel(initializeViewModel.MstPageModel)
                .SetPageComponentWidth(_pageComponent.PageWidth)
                .Build();

            await BattleFieldView.InitializeBattleField(
                _fieldViewConstructData,
                _pageComponent,
                this,
                cancellationToken);

            BattleFieldView.GenerateOutpost(initializeViewModel.PlayerOutpostModel);
            BattleFieldView.GenerateOutpost(initializeViewModel.EnemyOutpostModel);
        }

        void InitializeFieldUnit(InitializeViewModel initializeViewModel)
        {
            foreach (var characterUnitModel in initializeViewModel.InitialCharacterUnitModels)
            {
                OnSummonCharacterWithoutEffect(characterUnitModel);
            }
        }

        void InitializeGimmickObject(InitializeViewModel initializeViewModel)
        {
            foreach (var model in initializeViewModel.InitialInGameGimmickObjectModels)
            {
                BattleFieldView.GenerateGimmickObject(model);
            }
        }

        void InitializeDefenseTargetObject(InitializeViewModel initializeViewModel)
        {
            if (initializeViewModel.DefenseTargetModel.IsEmpty())
            {
                return;
            }

            BattleFieldView.GenerateDefenseTargetView(initializeViewModel.DefenseTargetModel);
        }

        void InitializeBattleSpeedButton(InitializeViewModel initializeViewModel)
        {
            _battleSpeedButton.SetBattleSpeed(initializeViewModel.BattleSpeed);
        }

        void InitializeEventComponent(InitializeViewModel initializeViewModel)
        {
            if (initializeViewModel.InGameType == InGameType.AdventBattle)
            {
                _eventComponent.InitializeAdventBattle(initializeViewModel.StageTimeModel);
                return;
            }

            if (initializeViewModel.InGameType == InGameType.Pvp)
            {
                _eventComponent.InitializePvp(initializeViewModel.StageTimeModel);
                return;
            }

            switch (initializeViewModel.QuestType)
            {
                case QuestType.Enhance:
                    _eventComponent.InitializeEnhanceQuest(initializeViewModel.StageTimeModel);
                    break;
                default:
                    // 1. ターゲットを守り抜け
                    // 2. n体以上撃破しよう
                    // 3. 制限時間以内にクリアしよう
                    // 4. 制限時間守り抜け
                    // 5. より早くファントムゲートを撃破しよう
                    if (!initializeViewModel.DefenseTargetModel.IsEmpty())
                    {
                        _eventComponent.InitializeDefenseTargetQuest();
                        break;
                    }

                    var battleEndModel = initializeViewModel.BattleEndModel;
                    if (battleEndModel.TryGetCondition<DefeatedEnemyCountBattleEndConditionModel>(out var condition))
                    {
                        _eventComponent.InitializeTotalDefeatCountQuest(condition);
                        break;
                    }

                    if (battleEndModel.TryGetCondition<DefeatUnitBattleEndConditionModel>(out var defeatUnitCondition))
                    {
                        _eventComponent.InitializeDefeatTargetEnemyQuest(defeatUnitCondition);
                        break;
                    }

                    var stageTimeModel = initializeViewModel.StageTimeModel;
                    if (stageTimeModel.Rule is InGameTimeRule.TimeLimitVictory or InGameTimeRule.TimeLimitDefeat)
                    {
                        _eventComponent.InitializeTimeLimit(stageTimeModel);
                        break;
                    }
                    if (stageTimeModel.Rule == InGameTimeRule.SpeedAttack)
                    {
                        _eventComponent.InitializeSpeedAttackQuest(stageTimeModel);
                        break;
                    }

                    _eventComponent.Hidden = true;
                    break;
            }
        }

        void InitializeCutInPlayer()
        {
            _cutInPlayer.Initialize(UnitImageContainer);
        }

        void InitializeMangaAnimationPlayer()
        {
            _mangaAnimationPlayer.Initialize(
                MangaAnimationContainer,
                _pageComponent,
                BattleFieldView,
                MangaAnimationFactory,
                TimeScaleController);
        }

        void InitializeRushPlayer()
        {
            _rushPlayer.Initialize(UnitImageContainer);
        }

        /// <summary> 初期化時やコンティニュー用のユニット生成処理。SEや召喚アニメを除外したシンプルな生成処理 </summary>
        public void OnSummonCharacterWithoutEffect(CharacterUnitModel characterUnitModel)
        {
            FieldUnitView fieldUnitView = BattleFieldView.GenerateCharacterUnitWithoutEffect(characterUnitModel);

            // 初期配置のキャラにもHPバーを表示する
            var fieldObjectViewPositionTracker = new FieldViewPositionTracker(
                fieldUnitView.ConditionComponentPosition,
                BattleFieldView.transform,
                _fieldViewConstructData);

            _pageComponent.AddUnitConditionComponent(
                fieldObjectViewPositionTracker,
                characterUnitModel.Id,
                characterUnitModel.BattleSide,
                characterUnitModel.Kind,
                characterUnitModel.CharacterId);
        }

        public void OnSummonCharacter(CharacterUnitModel characterUnitModel, Action onSummonComplete)
        {
            FieldUnitView fieldUnitView = BattleFieldView.GenerateCharacterUnit(characterUnitModel);

            DoAsync.Invoke(this, async ct =>
            {
                // SE
                if (!characterUnitModel.Transformation.IsTransformed())
                {
                    var se = characterUnitModel.BattleSide == BattleSide.Player
                        ? SoundEffectId.SSE_051_066
                        : SoundEffectId.SSE_051_067;

                    SoundEffectPlayer.Play(se);
                }

                await PlaySummonAnimation(
                    fieldUnitView,
                    characterUnitModel.SummonAnimationType,
                    characterUnitModel.AppearanceAttack.MainAttackElement.AttackDamageType,
                    characterUnitModel.IsBoss,
                    characterUnitModel.Transformation.IsTransformed(),
                    ct);

                var fieldObjectViewPositionTracker = new FieldViewPositionTracker(
                        fieldUnitView.ConditionComponentPosition,
                        BattleFieldView.transform,
                        _fieldViewConstructData);

                _pageComponent.AddUnitConditionComponent(
                    fieldObjectViewPositionTracker,
                    characterUnitModel.Id,
                    characterUnitModel.BattleSide,
                    characterUnitModel.Kind,
                    characterUnitModel.CharacterId);

                var isBoss = characterUnitModel.Kind == CharacterUnitKind.Boss;
                if (characterUnitModel.IsDefeatTarget)
                {
                    // 勝利条件となる撃破対象の敵へのターゲット表示
                    var fieldObjectViewTagPositionTracker = new FieldViewPositionTracker(
                        fieldUnitView.TagPosition,
                        BattleFieldView.transform,
                        _fieldViewConstructData);
                    _pageComponent.AddTargetTag(fieldObjectViewTagPositionTracker, isBoss);
                }
                else if (isBoss && !characterUnitModel.Transformation.IsTransformed())
                {
                    // ボスの場合タグをつける(降臨ボスのときはつけない)
                    var fieldObjectViewTagPositionTracker = new FieldViewPositionTracker(
                        fieldUnitView.TagPosition,
                        BattleFieldView.transform,
                        _fieldViewConstructData);
                    _pageComponent.AddBossTag(fieldObjectViewTagPositionTracker);
                }

                onSummonComplete?.Invoke();
            });
        }

        public async UniTask OnSummonSpecialUnit(
            SpecialUnitModel specialUnitModel,
            Action onStartSpecialAttack,
            Action onEndSpecialAttack,
            CancellationToken cancellationToken)
        {
            var specialUnitView = BattleFieldView.GenerateSpecialUnit(specialUnitModel);
            using var linkedTokenSource = CancellationTokenSource.CreateLinkedTokenSource(
                cancellationToken,
                specialUnitView.GetCancellationTokenOnDestroy());

            // 台詞
            SpeakSpecialUnitWhenSummon(specialUnitModel, linkedTokenSource.Token).Forget();

            // スペシャルユニット専用召喚エフェクトとキャラクターフェードイン
            await specialUnitView.Show(linkedTokenSource.Token);

            // 必殺技の溜めに遷移
            await specialUnitView.OnStartSpecialAttackCharge(linkedTokenSource.Token);

            // 必殺技発動
            await specialUnitView.OnSpecialAttack(linkedTokenSource.Token, onStartSpecialAttack,
                onEndSpecialAttack);

            // スペシャルユニット専用退去エフェクトとキャラクターフェードアウト
            await specialUnitView.Hide(linkedTokenSource.Token);
        }

        async UniTask SpeakSpecialUnitWhenSummon(SpecialUnitModel specialUnitModel, CancellationToken cancellationToken)
        {
            await UniTask.Delay(800, cancellationToken: cancellationToken);

            var speechBalloons = specialUnitModel.SpeechBalloons
                .Where(balloon => balloon.SpeechBalloon.ConditionType == SpeechBalloonConditionType.SpecialAttackCharge);

            foreach (var speechBalloon in speechBalloons)
            {
                SpeakSpecialUnit(speechBalloon);
            }
        }

        public void OnGenerateGimmickObject(InGameGimmickObjectModel inGameGimmickObjectModel)
        {
            BattleFieldView.GenerateGimmickObject(inGameGimmickObjectModel);
        }

        async UniTask PlaySummonAnimation(
            FieldUnitView fieldUnitView,
            SummonAnimationType summonAnimationType,
            AttackDamageType attackDamageType,
            bool isBoss,
            bool isTransformed,
            CancellationToken cancellationToken)
        {
            //SummonAnimationType増えてきたらメソッド分離する
            if (summonAnimationType == SummonAnimationType.Fall ||
                summonAnimationType == SummonAnimationType.Fall0 ||
                summonAnimationType == SummonAnimationType.Fall4)
            {
                var cacheParentTransform = fieldUnitView.SkeletonAnimation.transform.parent;
                var effect = summonAnimationType switch
                {
                    SummonAnimationType.Fall => BattleSummonEffectManager.Generate(BattleEffectId.AppearanceFall,
                        fieldUnitView.transform),
                    SummonAnimationType.Fall0 => BattleSummonEffectManager.Generate(BattleEffectId.AppearanceFall0,
                        fieldUnitView.transform),
                    SummonAnimationType.Fall4 => BattleSummonEffectManager.Generate(BattleEffectId.AppearanceFall4,
                        fieldUnitView.transform)
                };

                fieldUnitView.SkeletonAnimation.transform.SetParent(effect.UnitContentTransform, false);
                bool waitingBossSummonDelay = false;
                effect.OnBossSummonDelayEnd = () => { waitingBossSummonDelay = true; };
                effect.OnCompleted = () =>
                {
                    fieldUnitView.SkeletonAnimation.transform.SetParent(cacheParentTransform, false);
                    SoundEffectPlayer.Play(SoundEffectId.SSE_051_079);
                };
                effect.Play();

                //ボスのときだけアニメーショの砂埃にあわせてノックバックさせる(行動再開させる)
                if (isBoss)
                    await UniTask.WaitUntil(() => waitingBossSummonDelay, cancellationToken: cancellationToken);
            }

            if (summonAnimationType == SummonAnimationType.None)
            {
                if (isBoss && !isTransformed)
                {
                    _pageComponent.Shake(1f);
                    var effectId = attackDamageType == AttackDamageType.None
                        ? PageEffectId.AppearanceAttack01
                        : PageEffectId.AppearanceAttack02;

                    _pageComponent.GenerateEffect(effectId, fieldUnitView.FieldViewPos).Play();
                }
            }
        }

        public void OnAppearAttack(IAttackModel attackModel)
        {
        }

        public void OnUpdateAttacks(IReadOnlyList<IAttackModel> attackModels)
        {
            _pageComponent.UpdateAttackViews(attackModels);
            BattleFieldView.UpdateAttackViews(attackModels);
        }

        public void OnEndAttack(IAttackModel attackModel)
        {
            BattleFieldView.OnEndAttack(attackModel);
        }

        public void OnUpdateDeck(IReadOnlyList<DeckUnitViewModel> deckUnitViewModels)
        {
            _footerComponent.UpdateDeck(deckUnitViewModels);
        }

        public void OnUpdateFieldObjects(
            OutpostModel playerOutpostModel,
            OutpostModel enemyOutpostModel,
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            IReadOnlyList<SpecialUnitModel> specialUnitModels,
            DefenseTargetModel defenseTarget,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults,
            DamageDisplayFlag isDamageDisplay)
        {
            BattleFieldView.UpdateFieldObjects(
                playerOutpostModel,
                enemyOutpostModel,
                characterUnitModels,
                specialUnitModels,
                defenseTarget,
                appliedAttackResults,
                isDamageDisplay);
        }

        public void OnUpdatedBattlePoint(BattlePointModel battlePointModel)
        {
            _footerComponent.UpdatedBattlePoint(battlePointModel);
        }

        public void OnUpdateRush(RushModel rushModel)
        {
            _footerComponent.UpdateRush(rushModel);
        }

        public void OnUpdateOpponentRush(RushModel rushModel)
        {
            _footerComponent.UpdateOpponentRush(rushModel);
        }
        
        public void OnEffectBlocked(FieldObjectId fieldObjectId)
        {
            BattleFieldView.OnEffectBlocked(fieldObjectId);
        }
        
        public void OnPlaceItem(PlacedItemModel newPlacedItemModel)
        {
            // BattleFieldViewに対して行う
            BattleFieldView.GeneratePlacedItem(newPlacedItemModel);
        }
        
        public void OnRemoveConsumedItem(PlacedItemModel consumedItemModel)
        {
            // BattleFieldViewに対して行う
            BattleFieldView.RemoveConsumedItem(consumedItemModel);
        }
        
        public void OnRemovePlacedItem(PlacedItemModel placedItemModel)
        {
            // BattleFieldViewに対して行う
            BattleFieldView.RemovePlacedItem(placedItemModel);
        }
        
        public void OnSurvivedByGuts(FieldObjectId fieldObjectId)
        {
            BattleFieldView.OnSurvivedByGuts(fieldObjectId);
        }

        public void OnRushAttackPowerUp(
            BattleSide targetDeckBattleSide,
            FieldObjectId attackerId,
            PercentageM updatedPowerUp)
        {
            var unitView = BattleFieldView.GetCharacterUnitView(attackerId);
            if (unitView != null)
            {
                // UI側の威力上昇ウィンドウエフェクト表示
                var windowFieldViewPos = unitView.TagFieldViewPos;
                var windowEffectComponent = _pageComponent.GenerateEffect(PageEffectId.RushAttackPowerUp, windowFieldViewPos);
                windowEffectComponent.Play();

                // Page側の総攻撃威力上昇効果発動ユニットに表示するアイコンエフェクト
                var effectView = unitView.OnRushAttackPowerUp();

                // アイコンエフェクトアニメーション終了後に総攻撃ボタン側のアイコン表示と更新を行う
                var iconPos = windowEffectComponent.RectTransform.position -
                              Vector3.Scale(windowEffectComponent.RectTransform.lossyScale, WindowToIconDiffPos);

                if (targetDeckBattleSide == BattleSide.Player)
                {
                    // プレイヤー側の総攻撃ボタンにアイコンを表示
                    effectView.AddCompletedAction(() =>
                        _footerComponent.OnRushAttackPowerUp(iconPos, updatedPowerUp));
                }
                else
                {
                    // 対戦相手側の総攻撃ボタンにアイコンを表示
                    effectView.AddCompletedAction(() =>
                        _footerComponent.OnOpponentRushAttackPowerUp(iconPos, updatedPowerUp));
                }
            }

            var specialUnitView = BattleFieldView.GetSpecialUnitView(attackerId);
            if (specialUnitView != null)
            {
                // UI側の威力上昇ウィンドウエフェクト表示
                var windowFieldViewPos = specialUnitView.TagFieldViewPos;
                var windowEffectComponent = _pageComponent.GenerateEffect(PageEffectId.RushAttackPowerUp, windowFieldViewPos);
                windowEffectComponent.Play();

                // Page側の総攻撃威力上昇効果発動ユニットに表示するアイコンエフェクト
                var effectView = specialUnitView.OnRushAttackPowerUp();

                // アイコンエフェクトアニメーション終了後に総攻撃ボタン側のアイコン表示と更新を行う
                var iconPos = windowEffectComponent.RectTransform.position -
                              Vector3.Scale(windowEffectComponent.RectTransform.lossyScale, WindowToIconDiffPos);
                if (targetDeckBattleSide == BattleSide.Player)
                {
                    // プレイヤー側の総攻撃ボタンにアイコンを表示
                    effectView.AddCompletedAction(() =>
                        _footerComponent.OnRushAttackPowerUp(iconPos, updatedPowerUp));
                }
                else
                {
                    // 対戦相手側の総攻撃ボタンにアイコンを表示
                    effectView.AddCompletedAction(() =>
                        _footerComponent.OnOpponentRushAttackPowerUp(iconPos, updatedPowerUp));
                }
            }
        }

        public void OnCoolTimeVariation(
            BattleSide targetDeckBattleSide,
            StateEffectType stateEffectType,
            MasterDataId targetCharacterId)
        {
            var (uiEffectId, battleEffectId, soundEffectId) = GetEffectIds(stateEffectType);

            // 召喚ボタンにエフェクト再生
            if (uiEffectId != UIEffectId.None)
            {
                _footerComponent.OnCoolTimeVariation(targetDeckBattleSide, uiEffectId, targetCharacterId);
            }

            // 盤面上キャラにエフェクト再生(必殺ワザリキャストなど、盤面上にもEffect出したい場合想定
            if (battleEffectId != BattleEffectId.None)
            {
                var characterUnit = BattleFieldView.GetFirstCharacterUnitViewByCharacterId(targetCharacterId, targetDeckBattleSide);
                if (characterUnit != null)
                {
                    characterUnit.PlayTagPositionEffect(battleEffectId);
                }
            }

            // SE再生
            if (soundEffectId != SoundEffectId.None)
            {
                SoundEffectPlayer.Play(soundEffectId);
            }
        }

        public void OnGimmickObjectsRemoved(IReadOnlyList<InGameGimmickObjectModel> removedGimmickObjectModels)
        {
            BattleFieldView.OnGimmickObjectsRemoved(removedGimmickObjectModels);
        }

        public void OnGimmickObjectTransformationStarted(AutoPlayerSequenceElementId autoPlayerSequenceElementId)
        {
            var gimmickView = BattleFieldView.GetGimmickView(autoPlayerSequenceElementId);
            if (gimmickView != null)
            {
                gimmickView.OnTransformEffect();
            }
        }

        public void SetUpKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            _pageComponent.SetUpKomas(komaDictionary);
        }

        public void OnUpdateKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            _pageComponent.UpdateKomas(komaDictionary);
        }

        public void OnResetKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            _pageComponent.ResetKomas(komaDictionary);
        }

        public void OnSpeak(UnitSpeechBalloonModel speechBalloonModel)
        {
            var unitView = BattleFieldView.GetCharacterUnitView(speechBalloonModel.SpeakerId);
            if (unitView == null) return;

            _pageComponent
                .GenerateMangaEffect(_speechBalloonPrefab, unitView, false)
                ?.Setup(speechBalloonModel.SpeechBalloon.SpeechBalloonText)
                ?.Play();
        }

        public void SpeakSpecialUnit(UnitSpeechBalloonModel speechBalloonModel)
        {
            var specialUnitView = BattleFieldView.GetSpecialUnitView(speechBalloonModel.SpeakerId);
            if (specialUnitView == null) return;

            _pageComponent
                .GenerateMangaEffect(_speechBalloonPrefab, specialUnitView, false)
                ?.Setup(speechBalloonModel.SpeechBalloon.SpeechBalloonText)
                ?.Play();
        }

        public void OnUpdateTimeLimit(StageTimeModel stageTimeModel)
        {
            _eventComponent.UpdateTimeLimit(stageTimeModel);
        }

        public void OnTimeCountDown(TimeCountDown.EnumTimeCountDownType timeCountDownType)
        {
            // 残り時間表示
            _timeCountDownComponent.Play(timeCountDownType);
        }

        public void OnUpdateScore(
            InGameScore score,
            IReadOnlyList<ScoreCalculationResultModel> addedScoreModels,
            ScoreEffectVisibleFlag isScoreEffectVisible)
        {
            if (isScoreEffectVisible)
            {
                // コインクエスト以外ではスコア演出の都合上、スコアエフェクトが消えたタイミングで表示スコアに遅延反映する
                BattleFieldView.OnUpdateScore(addedScoreModels, () => _eventComponent.UpdateScore(score));
            }
            else
            {
                _eventComponent.UpdateScore(score);
            }
        }

        public void OnDefeatEnemy(
            StageEndConditionType stageEndConditionType,
            DefeatEnemyCount defeatedCount,
            DefeatEnemyCount endCondition)
        {
            switch (stageEndConditionType)
            {
                case StageEndConditionType.DefeatedEnemyCount:
                    _eventComponent.UpdateDefeatEnemyCount(defeatedCount, endCondition);
                    break;
                case StageEndConditionType.DefeatUnit:
                    _eventComponent.UpdateDefeatTargetEnemyProgress(defeatedCount, endCondition);
                    break;
            }
        }

        public void OnVictory()
        {
            BattleFieldView.OnVictory();
        }

        public void OnDefeat()
        {
            BattleFieldView.OnDefeat();
        }

        public void OnFinish()
        {
            BattleFieldView.OnFinish();
        }

        public void PlayerOutpostBroken()
        {
            BattleFieldView.PlayerOutpostBroken();
            _pageComponent.ShakeKomaAt(BattleFieldView.PlayerOutpostView.FieldViewPos, 1f);
        }

        public void EnemyOutpostBroken()
        {
            BattleFieldView.EnemyOutpostBroken();
            _pageComponent.ShakeKomaAt(BattleFieldView.EnemyOutpostView.FieldViewPos, 1f);
        }

        public void RecoverPlayerOutpost()
        {
            BattleFieldView.RecoverPlayerOutpost();
        }

        public void ShowDefeatUI()
        {
            _defeatUIObject.Hidden = false;
        }

        public void SetBattleSpeed(BattleSpeed battleSpeed)
        {
            _battleSpeedButton.SetBattleSpeed(battleSpeed);
        }

        public void SwitchAutoButton(InGameAutoEnabledFlag isAutoEnabled)
        {
            _autoButton.IsToggleOn = isAutoEnabled;
        }

        public void SetButtonsToFrontViewCanvas()
        {
            _autoButton.gameObject.transform.SetParent(_frontViewCanvas.transform);
            _settingButtonObj.gameObject.transform.SetParent(_frontViewCanvas.transform);
        }

        public void ResetMovedUI()
        {
            _autoButton.gameObject.transform.SetParent(_noFrontViewObject.transform);
            _settingButtonObj.gameObject.transform.SetParent(_noFrontViewObject.transform);
        }

        /// <summary> スペシャルユニットのコマ選択表示の開始 </summary>
        public void StartSpecialUnitSummonTargetSelection(
            Action<PageCoordV2> pageTouchCallback,
            SpecialUnitSummonKomaRange komaRange,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<KomaId> summoningKomaIds)
        {
            _pageComponent.TouchLayerHidden = false;
            _pageComponent.StartSpecialUnitKomaSelection(komaRange, komaDictionary, summoningKomaIds);
            _pageTouchCallback = pageTouchCallback;
        }

        public void EndKomaSelection()
        {
            _pageComponent.TouchLayerHidden = true;
            _pageComponent.EndKomaSelection();
        }

        public void StartSpecialUnitSkillConfirmationWindow()
        {
            _footerComponent.EnableTapGuardDuringTargetSelection();
            _background.GrayOut();
        }

        public void EndSpecialUnitSkillConfirmationWindow()
        {
            _footerComponent.DisableTapGuardDuringTargetSelection();
            _background.ResetGrayOut();
        }

        /// <summary> スペシャルユニット選択中用の選択可能コマ更新 </summary>
        public void UpdateKomaSelectable(
            SpecialUnitSummonKomaRange komaRange,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<KomaId> specialUnitKomaIds)
        {
            _pageComponent.UpdateKomaSelectable(komaRange, komaDictionary, specialUnitKomaIds);
        }

        /// <summary> スペシャルユニットの必殺技発動中に表示される、効果範囲内コマの強調表示 </summary>
        public void ShowHighlightKomaWithinSpecialCoordinateRange(CoordinateRange fieldViewCoordinateRange)
        {
            _pageComponent.ShowHighlightKomaWithinSpecialCoordinateRange(fieldViewCoordinateRange);
        }

        /// <summary> スペシャルユニットの必殺技発動中の強調表示から通常に戻す </summary>
        public void HideKomaHighlightWithinSpecialCoordinateRange()
        {
            _pageComponent.HideKomaHighlightWithinSpecialCoordinateRange();
        }

        public async UniTask ShowBlackCurtain(CancellationToken cancellationToken)
        {
            await _blackCurtainComponent.Show(cancellationToken);
        }

        public async UniTask ShowBlackCurtain(float alpha, CancellationToken cancellationToken)
        {
            await _blackCurtainComponent.Show(alpha, cancellationToken);
        }

        public async UniTask HideBlackCurtain(CancellationToken cancellationToken)
        {
            await _blackCurtainComponent.Hide(cancellationToken);
        }

        /// <summary>
        /// 画面全体用の半透明黒背景での暗転でなく個別に暗転する
        /// 現状スペシャルユニットにてDeckを対象とした必殺技発動時に発動ユニット以外暗転させるのに使用
        /// </summary>
        public async UniTask ShowIndividualBlackCurtain(CancellationToken cancellationToken)
        {
            BattleFieldView.ShowBlackCurtain();
            _footerComponent.EnableFadeFooterTapGuard();
            await _pageWithoutBlackCurtainComponent.Show(cancellationToken);
        }

        public async UniTask HideIndividualBlackCurtain(CancellationToken cancellationToken)
        {
            BattleFieldView.HideBlackCurtain();
            _footerComponent.DisableFadeFooterTapGuard();
            await _pageWithoutBlackCurtainComponent.Hide(cancellationToken);
        }

        public void ShowBattleFieldBlackCurtain()
        {
            BattleFieldView.ShowBlackCurtain();
        }

        public void HideBattleFieldBlackCurtain()
        {
            BattleFieldView.HideBlackCurtain();
        }

        public void EnableTapGuard()
        {
            _tapGuardObject.Hidden = false;
        }

        public void DisableTapGuard()
        {
            _tapGuardObject.Hidden = true;
        }

        public void EnablePageTapGuard()
        {
            _pageTapGuardObject.Hidden = false;
        }

        public void DisablePageTapGuard()
        {
            _pageTapGuardObject.Hidden = true;
        }

        public void EnableHeaderTapGuard()
        {
            _headerTapGuardObject.Hidden = false;
        }

        public void DisableHeaderTapGuard()
        {
            _headerTapGuardObject.Hidden = true;
        }

        public void EnableFooterTapGuard()
        {
            _footerComponent.EnableTapGuard();
        }

        public void DisableFooterTapGuard()
        {
            _footerComponent.DisableTapGuard();
        }

        public void DisableFadeFooterTapGuard()
        {
            _footerComponent.DisableFadeFooterTapGuard();
        }

        public void ResetTouchCallBack()
        {
            _pageTouchCallback = null;
        }

        public void ShowTapToSkip(float delay)
        {
            _tapToSkipComponent.Show(delay);
        }

        public void HideTapToSkip()
        {
            _tapToSkipComponent.Hide();
        }

        public void ShowTapToStartBattle()
        {
            _tapToStartBattle.Hidden = false;
        }

        public void HideTapToStartBattle()
        {
            _tapToStartBattle.Hidden = true;
        }

        public void SetDefenseTargetHighlight(bool isHighlight)
        {
            BattleFieldView.SetDefenseTargetHighlight(isHighlight);

            if (isHighlight)
            {
                _eventComponent.ShowDefenseTargetHighlight();
            }
            else
            {
                _eventComponent.HideDefenseTargetHighlight();
            }
        }

        public void SetPlayerOutpostHpHighlight(bool isHighlight)
        {
            BattleFieldView.SetPlayerOutpostHpHighlight(isHighlight);
        }

        public void ChangeDeckMode()
        {
            _footerComponent.ChangeDeckMode();

            //DeckComponent.ChangeDeckModeでImmediateで高さ更新した後の情報でpageLayoutの高さを変更する
            LayoutRebuilder.ForceRebuildLayoutImmediate(_footerRectTransform);
            _pageLayoutGroup.padding.bottom = (int)_footerRectTransform.sizeDelta.y;

            LayoutRebuilder.ForceRebuildLayoutImmediate(_pageLayoutGroup.transform as RectTransform);
            _pageComponent.SetupLayout();
        }
        
        public void SetDamageDisplay(DamageDisplayFlag isDamageDisplay)
        {
            _pageComponent.SetDamageDisplayVisible(isDamageDisplay);
        }

        public PageCoordV2 GetDisplayingPageCenter()
        {
            return _pageComponent.GetDisplayingPageCenter();
        }

        public async UniTask ScrollPage(
            FieldViewCoordV2 targetPos,
            float scrollMaxDuration,
            CancellationToken cancellationToken,
            bool isIndependentUpdate = false,
            bool isUnrestricted = false)
        {
            await _pageComponent.ScrollTo(targetPos, scrollMaxDuration, cancellationToken, isIndependentUpdate, isUnrestricted);
        }

        public async UniTask ScrollPage(
            float targetNormalizedPosY,
            float duration,
            CancellationToken cancellationToken,
            bool isIndependentUpdate = false)
        {
            await _pageComponent.ScrollTo(targetNormalizedPosY, duration, cancellationToken, isIndependentUpdate);
        }

        public async UniTask ScalePage(float scale, float duration, CancellationToken cancellationToken)
        {
            await _pageComponent.ScalePageTo(scale, duration, cancellationToken);
        }

        public async UniTask ScalePage(
            FieldViewCoordV2 targetPos,
            float scale,
            float duration,
            CancellationToken cancellationToken)
        {
            await _pageComponent.ScalePageTo(targetPos, scale, duration, cancellationToken);
        }

        public void ScalePage(FieldViewCoordV2 targetPos, float scale)
        {
            _pageComponent.ScalePageTo(targetPos, scale);
        }

        public void ResetPageScale()
        {
            _pageComponent.ResetPageScale();
        }

        public void StartUnitTracking(FieldObjectId id)
        {
            var unitView = BattleFieldView.GetCharacterUnitView(id);
            if (unitView == null) return;

            var tracker = new FieldViewPositionTracker(
                unitView.TrackingPosition,
                BattleFieldView.transform,
                _fieldViewConstructData);

            _pageComponent.StartTracking(tracker);
        }

        public void EndUnitTracking()
        {
            _pageComponent.EndTracking();
        }

        public MultipleSwitchHandler ExpandKomaSetFieldImage(FieldViewCoordV2 targetPos)
        {
            return _pageComponent.ExpandKomaSetFieldImage(targetPos);
        }

        public MultipleSwitchHandler PauseBattleField()
        {
            var handler = MultipleSwitchController.CreateHandler();

            BattleFieldView.Pause(handler);
            _pageComponent.Pause(handler);
            _mangaAnimationPlayer.Pause(handler);
            _cutInPlayer.Pause(handler);
            _rushPlayer.Pause(handler);
            // 盤面想定ではあるが、UIエフェクト再生が止まらないのは違和感あるため停止
            UIEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public MultipleSwitchHandler PauseWithoutPlayerUnit()
        {
            var handler = MultipleSwitchController.CreateHandler();

            BattleFieldView.PauseWithoutPlayerUnit(handler);
            _pageComponent.Pause(handler);
            _mangaAnimationPlayer.Pause(handler);
            _cutInPlayer.Pause(handler);
            _rushPlayer.Pause(handler);
            UIEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public MultipleSwitchHandler PauseWithout(FieldObjectId id)
        {
            var handler = MultipleSwitchController.CreateHandler();

            BattleFieldView.PauseWithout(handler, id);
            _pageComponent.Pause(handler);
            _mangaAnimationPlayer.Pause(handler);
            _cutInPlayer.Pause(handler);
            _rushPlayer.Pause(handler);
            UIEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public MultipleSwitchHandler PauseWithoutDarknessClear()
        {
            var handler = MultipleSwitchController.CreateHandler();

            BattleFieldView.Pause(handler);
            _pageComponent.PauseWithoutDarknessClear(handler);
            _mangaAnimationPlayer.Pause(handler);
            _cutInPlayer.Pause(handler);
            _rushPlayer.Pause(handler);
            UIEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public MultipleSwitchHandler PauseWithoutDarknessClearAndPlayerUnit()
        {
            var handler = MultipleSwitchController.CreateHandler();

            BattleFieldView.PauseWithoutPlayerUnit(handler);
            _pageComponent.PauseWithoutDarknessClear(handler);
            _mangaAnimationPlayer.Pause(handler);
            _cutInPlayer.Pause(handler);
            _rushPlayer.Pause(handler);
            UIEffectManager.PauseAllEffects(handler);

            return handler;
        }

        public async UniTask PlayMangaAnimation(
            MangaAnimationAssetKey assetKey,
            MangaAnimationSpeed animationSpeed,
            CancellationToken cancellationToken)
        {
            await _mangaAnimationPlayer.Play(assetKey, animationSpeed, cancellationToken);
        }

        public async UniTask PlayCutIn(
            CharacterColor unitColor,
            UnitAssetKey unitAssetKey,
            UnitAttackViewInfo attackViewInfo,
            CancellationToken cancellationToken)
        {
            await _cutInPlayer.Play(unitColor, unitAssetKey, attackViewInfo, cancellationToken);
        }

        public async UniTask PlayRush(
            IReadOnlyList<UnitAssetKey> unitAssetKeys,
            IReadOnlyList<UnitAssetKey> specialUnitAssetKey,
            PercentageM specialUnitBonus,
            Action unpauseAction,
            BattleSide battleSide,
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType,
            CancellationToken cancellationToken)
        {
            if (battleSide == BattleSide.Enemy)
            {
                await _rushPlayer.PlayPvpOpponentRush(
                    unitAssetKeys,
                    unpauseAction,
                    chargeCount,
                    calculatedRushAttackPower,
                    cancellationToken);
            }
            else
            {
                await _rushPlayer.Play(
                    unitAssetKeys,
                    specialUnitAssetKey,
                    specialUnitBonus,
                    unpauseAction,
                    chargeCount,
                    calculatedRushAttackPower,
                    rushEvaluationType,
                    cancellationToken);
            }
        }

        public async UniTask PlayBattleStartNoiseAnimation(CancellationToken cancellationToken)
        {
            await _battleStartNoiseComponent.Play(cancellationToken);
        }

        public async UniTask TransformUnit(
            FieldObjectId beforeUnitId,
            FieldObjectId afterUnitId,
            CancellationToken cancellationToken)
        {
            await BattleFieldView.TransformUnit(beforeUnitId, afterUnitId, cancellationToken);
        }

        public void SetUnitVisible(FieldObjectId id, bool visible)
        {
            BattleFieldView.SetUnitVisible(id, visible);
        }

        public void SetUnitConditionVisible(bool isVisible)
        {
            _pageComponent.SetUnitConditionVisible(isVisible);
        }

        public FieldViewCoordV2 GetEnemyOutpostViewPos()
        {
            return BattleFieldView.EnemyOutpostView.FieldViewPos;
        }

        public FieldViewCoordV2 GetDefenseTargetViewPos()
        {
            return BattleFieldView.DefenseTargetPos;
        }

        public FieldUnitView GetUnitView(FieldObjectId id)
        {
            return BattleFieldView.GetCharacterUnitView(id);
        }

        public FieldViewCoordV2 GetUnitViewPos(FieldObjectId id)
        {
            return BattleFieldView.GetCharacterUnitViewPos(id);
        }

        public FieldViewCoordV2 GetUnitViewTrackingPos(FieldObjectId id)
        {
            return BattleFieldView.GetCharacterUnitViewTrackingPos(id);
        }

        public Vector2 GetEnemyOutpostKomaSetCenterPos()
        {
            return _pageComponent.GetKomaSetCenterPos(BattleFieldView.EnemyOutpostView.FieldViewPos);
        }

        public void SetUnrestrictedScroll()
        {
            _pageComponent.SetUnrestrictedScroll();
        }

        public void SetElasticScroll()
        {
            _pageComponent.SetElasticScroll();
        }

        public bool IsDarknessKomaCleared(FieldViewCoordV2 position)
        {
            return _pageComponent.IsDarknessKomaCleared(position);
        }

        public void HideOptionButton()
        {
            _optionButton.Hidden = true;
        }

        public void HideAutoButton()
        {
            _autoButton.Hidden = true;
        }

        public void HideSpeedButton()
        {
            _battleSpeedButton.Hidden = true;
        }

        public void HideRushGauge()
        {
            _footerComponent.HideRushButton();
        }

        public async UniTask LoadTutorialIntroductionManga(CancellationToken cancellationToken)
        {
            await _tutorialIntroductionMangaManager.Load(cancellationToken);
        }

        MultipleSwitchHandler IScreenFlashTrackClipDelegate.StartFlash()
        {
            return _flashComponent.StartFlash();
        }

        UnityEngine.Object IScreenFlashTrackClipDelegate.GetObjectForBinding()
        {
            return this;
        }

        void OnPageTouchInternal(PageCoordV2 pos)
        {
            _pageTouchCallback?.Invoke(pos);
        }

        (UIEffectId uiEffectId, BattleEffectId battleEffectId, SoundEffectId soundEffectId) GetEffectIds(StateEffectType stateEffectType)
        {
            return stateEffectType switch
            {
                StateEffectType.SpecialAttackCoolTimeShorten => (
                    UIEffectId.SpecialAttackCoolTimeShorten,
                    BattleEffectId.SpecialAttackCoolTimeShorten,
                    SoundEffectId.SSE_051_006),
                StateEffectType.SpecialAttackCoolTimeExtend => (
                    UIEffectId.SpecialAttackCoolTimeExtend,
                    BattleEffectId.SpecialAttackCoolTimeExtend,
                    SoundEffectId.SSE_051_009),
                StateEffectType.SummonCoolTimeShorten => (
                    UIEffectId.SummonCoolTimeShorten,
                    BattleEffectId.None,
                    SoundEffectId.SSE_051_006),
                StateEffectType.SummonCoolTimeExtend => (
                    UIEffectId.SummonCoolTimeExtend,
                    BattleEffectId.None,
                    SoundEffectId.SSE_051_009),
                _ => (UIEffectId.None, BattleEffectId.None, SoundEffectId.None),
            };
        }

#if GLOW_INGAME_DEBUG
        void UpdateDebugInfo()
        {
            _debugInfoComponent.FPS = FPSProfiler.GetFPS().Value;
            _debugInfoComponent.MemoryUsage = MemoryProfiler.GetUsageMemorySize().Value;
            _debugInfoComponent.CharacterUnitCount = BattleFieldView.CharacterUnitViewList.Count;
        }
#endif
    }
}
