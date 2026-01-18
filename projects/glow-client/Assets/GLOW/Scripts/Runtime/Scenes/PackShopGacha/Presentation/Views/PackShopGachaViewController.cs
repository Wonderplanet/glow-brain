using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.PackShop.Presentation.Views;
using GLOW.Scenes.PackShopGacha.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.PackShopGacha.Presentation.Views
{
    public class PackShopGachaViewController :
        UIViewController<PackShopGachaView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate,
        IEscapeResponder
    {
        public record Argument(MasterDataId TicketId, IPackShopViewController PackShopViewController, MasterDataId MstPackId);

        [Inject] IPackShopGachaViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        IReadOnlyList<PackShopGachaCellViewModel> _gachaBannerModels;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.GachaBannerList.Delegate = this;
            ActualView.GachaBannerList.DataSource = this;

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public void SetUp(PackShopGachaViewModel viewModel)
        {
            _gachaBannerModels = viewModel.GachaCellViewModels;
            ActualView.GachaBannerList.ReloadData();
        }

        public void MoveScrollToTargetPos(float targetPosY)
        {
            ActualView.MoveScrollToTargetPos(targetPosY);
        }

        public float GetNormalizedPos()
        {
            return ActualView.ScrollRect.verticalNormalizedPosition;
        }
        
        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _gachaBannerModels?.Count ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<PackShopGachaCell>();
            var viewModel = _gachaBannerModels[indexPath.Row];
            cell.Setup(viewModel, OnBannerTappedAction);
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath) { }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier) { }

        void OnBannerTappedAction(MasterDataId gachaId)
        {
            ViewDelegate.OnBannerTapped(gachaId);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            Close();
            return true;
        }

        void Close()
        {
            ViewDelegate.OnClose();
        }

        [UIAction]
        void OnCloseButtonTapped()
        {
            Close();
        }
    }
}
