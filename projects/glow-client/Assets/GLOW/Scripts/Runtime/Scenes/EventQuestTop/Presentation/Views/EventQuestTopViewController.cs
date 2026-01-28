using System;
using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.BoxGacha.Domain.ValueObject;
using GLOW.Scenes.EventQuestSelect.Domain.UseCase;
using GLOW.Scenes.EventQuestTop.Presentation.Components;
using GLOW.Scenes.EventQuestTop.Presentation.ViewModels;
using GLOW.Scenes.Home.Domain.AssetLoader;
using GLOW.Scenes.Home.Presentation.Views;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using UIKit;
using UnityEngine;
using Wonderplanet.UIHaptics.Presentation;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Presentation.Views
{
    /// <summary>
    /// 42_イベントステージ
    /// 　42-1_イベントクエスト
    /// 　　42-1-3_イベントクエストステージ選択
    /// </summary>
    public class EventQuestTopViewController : UIViewController<EventQuestTopView>,IEscapeResponder
    {
        public record Argument(MasterDataId MstQuestGroupId);

        [Inject] IEventQuestTopViewDelegate ViewDelegate { get; }
        [Inject] IHomeViewDelegate HomeViewDelegate { get; }
        [Inject] IHapticsPresenter HapticsPresenter { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IBackGroundSpriteLoader BackGroundSpriteLoader { get; }
        [Inject] IBackGroundSpriteContainer BackGroundSpriteContainer { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }

        EventQuestTopViewModel _viewModel;
        EventQuestTopStageSelectControl _questTopStageSelectControl;
        EventQuestTopElementViewModel _selectedQuestModel;

        public bool AssetLoadEnded { get; private set; }
        public bool HideLoadingView { get; set; }
        bool _usableBackKey;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            EscapeResponderRegistry.Register(this);
            ActualView.ReleaseAnimation.gameObject.SetActive(false);//表示しているとローディング画面で重なるのでfalse
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();

            //homeMainViewが非表示、かつ一度アプリが非アクティブになると触覚FBが止まるので再開させる
            HapticsPresenter.SyncRestartEngine();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void SetEventExchangeShopActive(bool isActive)
        {
            ActualView.SetEventExchangeShopButton(isActive);
        }

        public async UniTask Initialize(
            EventQuestTopViewModel viewModel,
            CancellationToken ct)
        {
            _viewModel = viewModel;

            // 順番依存.1
            InitializeStageSelectCarousel(viewModel);

            // 順番依存.2：各種セットアップ
            var initUnitContainer = InitUnitContainer(ct, viewModel.Units);
            var initBackGroundContainer = InitEventBackGroundContainer(ct, viewModel.Stages);
            ActualView.SetUpView(viewModel);
            await UniTask.WhenAll(initUnitContainer, initBackGroundContainer);

            var initViewModel = GetInitSelectViewModel(viewModel.Stages, viewModel.InitialSelectStageMstStageId);
            ActualView.SetUpBackGround(GetBackGroundSprite(initViewModel.EventTopBackGroundAssetPath));
            ActualView.SetUpUnits(viewModel.Units
                .Select(a => InstantiateUnitImage(a.UnitImageAssetPath))
                .ToList()
            );

            AssetLoadEnded = true;
            await UniTask.WaitUntil(() => HideLoadingView, cancellationToken: ct);

            //ステージ開放演出
            //順番依存.3：CarouselViewのbuild(CarouselView.DataSource)が呼ばれた後に処理
            await TryShowStageReleaseAnimation(viewModel, ct);

            // 順番依存.4：クエスト開放演出は最後に再生
            await TryShowQuestReleaseAnimation(viewModel, ct);
        }

        async UniTask InitUnitContainer(CancellationToken ct,IReadOnlyList<EventQuestTopUnitViewModel> units)
        {
            await UniTask.WhenAll(
                units
                    .Select(a => UnitImageLoader.Load(ct, a.UnitImageAssetPath))
                );
        }

        async UniTask InitEventBackGroundContainer(CancellationToken ct,IReadOnlyList<EventQuestTopElementViewModel> stages)
        {
            var distinctBackGroundAssetPaths= stages
                .Select(s => s.EventTopBackGroundAssetPath)
                .Distinct()
                .ToList();
            await UniTask.WhenAll(
                distinctBackGroundAssetPaths
                    .Select(a => BackGroundSpriteLoader.Load(ct, a))
                );
        }

        UnitImage InstantiateUnitImage(UnitImageAssetPath imageAssetPath)
        {
            var go = UnitImageContainer.Get(imageAssetPath);
            var characterImage = go.GetComponent<UnitImage>();
            characterImage.SortingOrder = 0;
            return characterImage;
        }

        Sprite GetBackGroundSprite(KomaBackgroundAssetPath assetPath)
        {
            return BackGroundSpriteContainer.Get(assetPath);
        }

        void InitializeStageSelectCarousel(EventQuestTopViewModel viewModel)
        {
            // カルーセル初期化
            _questTopStageSelectControl = new EventQuestTopStageSelectControl(
                ActualView,
                viewModel,
                OnSelect,
                ViewDelegate);
            ActualView.InitializeCarousel(_questTopStageSelectControl, _questTopStageSelectControl, HapticsPresenter);

            // 初期表示処理
            var initSelectModel = GetInitSelectViewModel(viewModel.Stages, viewModel.InitialSelectStageMstStageId);
            var shouldShowButton = GetShowButtonStatus(initSelectModel, viewModel.Stages);
            OnSelect(initSelectModel,shouldShowButton.shouldShowLeftButton, shouldShowButton.shouldShowRightButton);
        }

        (bool shouldShowLeftButton, bool shouldShowRightButton) GetShowButtonStatus(
            EventQuestTopElementViewModel selected,
            IReadOnlyList<EventQuestTopElementViewModel> stages)
        {
            var index = stages.IndexOf(selected);
            return (0 < index, index < stages.Count - 1);
        }

        EventQuestTopElementViewModel GetInitSelectViewModel(
            IReadOnlyList<EventQuestTopElementViewModel> stages, MasterDataId selectedStageId)
        {
            var model = stages.FirstOrDefault(s => s.MstStageId == selectedStageId,EventQuestTopElementViewModel.Empty);
            return model.IsEmpty()
                ? stages.First()
                : model;
        }


        public void SetMissionBadge(bool isExistReceivableMission)
        {
            ActualView.SetMissionBadge(isExistReceivableMission);
        }
        
        public void SetBoxGachaBadge(BoxGachaDrawableFlag isBoxGachaDrawable)
        {
            ActualView.SetBoxGachaBadge(isBoxGachaDrawable);
        }
        
        public void SetCurrentPartyName(PartyName partyName)
        {
            ActualView.SetCurrentPartyName(partyName);
        }

        async UniTask TryShowStageReleaseAnimation(EventQuestTopViewModel viewModel, CancellationToken ct)
        {
            ActualView.ReleaseAnimation.gameObject.SetActive(viewModel.ShowStageReleaseAnimation.ShouldShow);

            if (!viewModel.ShowStageReleaseAnimation.ShouldShow)
            {
                _usableBackKey = true;
                return;
            }

            var cell = ActualView.CarouselView.SelectedCell as EventQuestTopStageCell;
            if (cell == null)
            {
                throw new Exception("Trying show stage release animation but cell is null.");
                _usableBackKey = true;
            }

            ActualView.ReleaseAnimation.OnStageReleaseEventAction = () => cell.ReleasedGameObject.SetActive(true);
            cell.ReleasedGameObject.SetActive(false);

            await StartStageReleaseAnimation(ct);
        }

        async UniTask TryShowQuestReleaseAnimation(EventQuestTopViewModel viewModel, CancellationToken cancellationToken)
        {
            foreach(var questName in viewModel.NewReleaseQuestNames)
            {
                var toast = CommonToastWireFrame.ShowScreenCenterToast(ZString.Format("【{0}】が開放されました！", questName));

                // 表示終了待ち。一度IsShownがtrueになったら、falseになるまで待つ
                await UniTask.WaitUntil(() => toast.IsShown, cancellationToken: cancellationToken);
                await UniTask.WaitUntil(() => !toast.IsShown, cancellationToken: cancellationToken);
            }
        }

        async UniTask StartStageReleaseAnimation(CancellationToken ct)
        {
            HomeViewDelegate.ShowTapBlock(true, ActualView.InvertMaskRect, 0f);
            var animationTime = 2.2f;
            var grayOutTransitionStartTime = 1.2f;
            var startDelay = 0.5f;
            await UniTask.Delay(TimeSpan.FromSeconds(startDelay), cancellationToken:ct);
            ActualView.ReleaseAnimation.ShowAnimation();

            await UniTask.Delay(TimeSpan.FromSeconds(grayOutTransitionStartTime), cancellationToken:ct);

            var duration = animationTime - grayOutTransitionStartTime;
            HomeViewDelegate.HideTapBlock(true, duration);

            var endDelay = animationTime - startDelay - grayOutTransitionStartTime;
            await UniTask.Delay(TimeSpan.FromSeconds(endDelay), cancellationToken:ct);
            ActualView.ReleaseAnimation.gameObject.SetActive(false);
            _usableBackKey = true;
        }

        void OnSelect(EventQuestTopElementViewModel selected, bool shouldShowLeftButton, bool shouldShowRightButton)
        {
            _selectedQuestModel = selected;
            ActualView.SetStageConsumeStaminaText(selected);

            ActualView.SetUpStageSelect(
                selected.RecommendedLevel,
                shouldShowLeftButton,
                shouldShowRightButton,
                selected.StageReleaseStatus.IsReleased);

            ActualView.SetUpSpeedAttackRecord(selected.SpeedAttackViewModel);
            ActualView.SetVisibleSpecialRuleButton(selected.ExistsSpecialRule);
            ActualView.SetStaminaBoostBalloon(selected.StaminaBoostBalloonType);

            ActualView.SetUpBackGround(GetBackGroundSprite(selected.EventTopBackGroundAssetPath));
        }


        [UIAction]
        void OnMissionButtonTapped()
        {
            ViewDelegate.OnMissionButtonTapped();
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
        [UIAction]
        void OnStageRightButtonTapped()
        {
            if (_questTopStageSelectControl.CanMoveCarousel(CarouselDirection.Right))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                _questTopStageSelectControl?.MoveRight();
            }
        }
        [UIAction]
        void OnStageLeftButtonTapped()
        {
            if ( _questTopStageSelectControl.CanMoveCarousel(CarouselDirection.Left))
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
                _questTopStageSelectControl?.MoveLeft();
            }
        }

        [UIAction]
        void OnStageStartButtonTapped()
        {
            ViewDelegate.OnStageStart(this, _selectedQuestModel);
        }

        [UIAction]
        void OnPartyEditButtonTapped()
        {
            ViewDelegate.OnPartyEditButtonTapped(_selectedQuestModel.MstStageId);
        }


        [UIAction]
        void OnInGameSpecialRuleButtonTapped()
        {
            ViewDelegate.OnInGameSpecialRuleTapped(_selectedQuestModel.MstStageId);
        }

        [UIAction]
        void OnUnit1Tapped()
        {
            if(_viewModel.Units.Count < 1) return;

            var viewModel = _viewModel.Units[0];
            ActualView.TapUnit(0,viewModel,1f, View.GetCancellationTokenOnDestroy()).Forget();
        }
        [UIAction]
        void OnUnit2Tapped()
        {
            if(_viewModel.Units.Count < 2) return;

            var viewModel = _viewModel.Units[1];
            ActualView.TapUnit(1,viewModel,1f, View.GetCancellationTokenOnDestroy()).Forget();
        }
        [UIAction]
        void OnUnit3Tapped()
        {
            if(_viewModel.Units.Count < 3) return;

            var viewModel = _viewModel.Units[2];
            ActualView.TapUnit(2,viewModel,1f, View.GetCancellationTokenOnDestroy()).Forget();
        }

        [UIAction]
        void OnEventExchangeShopButtonTapped()
        {
            ViewDelegate.OnEventExchangeShopButtonTapped();
        }
        
        [UIAction]
        void OnBoxGachaButtonTapped()
        {
            ViewDelegate.OnBoxGachaButtonTapped();
        }

        bool IEscapeResponder.OnEscape()
        {
            if(View.Hidden) return false;
            if (_usableBackKey) return false;

            SystemSoundEffectProvider.PlaySeTap();
            CommonToastWireFrame.ShowInvalidOperationMessage();
            return true;
        }
    }
}
