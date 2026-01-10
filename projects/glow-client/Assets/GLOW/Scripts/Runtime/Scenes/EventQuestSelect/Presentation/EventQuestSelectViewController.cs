using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Scenes.EventQuestSelect.Domain.ValueObject;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Scenes.EventQuestSelect.Presentation
{
    /// <summary>
    /// 42_イベントステージ
    /// 　42-1_イベントクエスト
    /// 　　42-1-2_いいジャン祭トップ画面（クエスト選択画面）
    /// </summary>
    public class EventQuestSelectViewController : UIViewController<EventQuestSelectView>,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        public record Argument(MasterDataId MstEventId);

        [Inject] IEventQuestSelectViewDelegate ViewDelegate { get; }
        [Inject] IEventQuestBackGroundLoader BackGroundLoader { get; }
        EventQuestSelectViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ActualView.Initialize();
            ActualView.InitCollectionView(this, this);
            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.UpdateMissionBadge();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ActualView.PlayChildScaler();
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }

        public void SetMissionBadge(bool unReceiveMissionReward)
        {
            ActualView.SetMissionBadgeActive(unReceiveMissionReward);
        }

        public void SetEventExchangeShopActive(bool isActive)
        {
            ActualView.SetEventExchangeShopActive(isActive);
        }

        public void SetUpView(EventQuestSelectViewModel viewModel)
        {
            _viewModel = viewModel;

            DoAsync.Invoke(ActualView.GetCancellationTokenOnDestroy(), async ct =>
            {
                var bgComponent = await BackGroundLoader.LoadBackGround(_viewModel.EventAssetKey, ct);
                ActualView.SetBackground(bgComponent);
                ActualView.SetCollectionView(_viewModel.Quests);
                ActualView.SetRemainingAtText(_viewModel.RemainingAtText);
                ActualView.SetCellTitleEventOpenText(_viewModel.IsOpen);
                ActualView.SetEventCampaignBalloon(
                    _viewModel.RemainingEventCampaignTimeSpan);
                ActualView.SetAdventBattleButtonActive(
                    viewModel.AdventBattleOpenStatus,
                    viewModel.AdventBattleName,
                    viewModel.AdventBattleRemainTimeSentence);
            });
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var model = _viewModel.Quests[indexPath.Row];
            if(!model.IsOpen())
            {
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_013);
                CommonToastWireFrame.ShowScreenCenterToast(model.UnlockRequirementDescription.Value).Show();
                return;
            }

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            ViewDelegate.OnEventQuestButtonTapped(model.MstQuestGroupId);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
            //no use.
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.Quests.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var model = _viewModel.Quests[indexPath.Row];
            var cell = collectionView.DequeueReusableCell<EventQuestSelectCell>();
            cell.SetUpCell(model);
            return cell;
        }

        [UIAction]
        void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }
        [UIAction]
        void OnMissionButtonTapped()
        {
            ViewDelegate.OnMissionButtonTapped();
        }

        [UIAction]
        void OnAdventBattleButtonTapped()
        {
            if(_viewModel.AdventBattleOpenStatus.Value == AdventBattleOpenStatusType.RankLocked)
            {
                CommonToastWireFrame.ShowScreenCenterToast(_viewModel.AdventBattleOpenSentence.Value).Show();
                return;
            }
            ViewDelegate.OnAdventBattleButtonTapped();
        }

        [UIAction]
        void OnEventExchangeShopButtonTapped()
        {
            ViewDelegate.ShowEventExchangeShop();
        }
    }
}
