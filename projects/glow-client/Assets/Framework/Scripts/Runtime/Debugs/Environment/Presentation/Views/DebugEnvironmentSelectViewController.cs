using System;
using UIKit;
using UnityEngine;
using WPFramework.Debugs.Environment.Presentation.ViewModels;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using WPFramework.Presentation.Views;
using Zenject;

namespace WPFramework.Debugs.Environment.Presentation.Views
{
    public sealed class DebugEnvironmentSelectViewController : UIViewController<DebugEnvironmentSelectView>, IUICollectionViewDataSource, IUICollectionViewDelegate, IEscapeResponder
    {
        [Serializable]
        public record Argument(Action OnDeleted, Action OnNotDeleted)
        {
            public Action OnDeleted { get; } = OnDeleted;
            public Action OnNotDeleted { get; } = OnNotDeleted;
        }

        [Inject] IDebugEnvironmentSelectViewDelegate SelectViewDelegate { get; }

        [Inject] Argument Argc { get; set; }

        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        DebugEnvironmentViewListModel _environmentViewModel;

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);

            // HACK: SystemCanvasにモーダル表示されているため999より下に下がらずに1000番台で表示されてしまう
            //       そのため、モーダル表示されている場合はモーダルの上に表示されるようにするためSortingOrderをいじる
            var canvas = ActualView.GetComponentInParent<Canvas>(includeInactive: true);
            if (!canvas)
            {
                return;
            }
            if (canvas.sortingOrder >= (int)OverlayCanvasSortingOrder.SystemCanvasModal)
            {
                canvas.sortingOrder = (int)OverlayCanvasSortingOrder.SystemCanvas - 1;
            }

            EscapeResponderRegistry.Bind(this, ActualView);
        }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            SelectViewDelegate.OnViewDidLoad();

            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public void SetEnvironmentViewModel(DebugEnvironmentViewListModel environmentViewModel)
        {
            _environmentViewModel = environmentViewModel;
            ActualView.CollectionView.ReloadData();
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _environmentViewModel?.Environments?.Length ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<DebugEnvironmentSelectCell>();
            var environmentData = _environmentViewModel?.Environments[indexPath.Row];
            if (environmentData == null)
            {
                return cell;
            }

            cell.DescriptionText = environmentData.Description;
            cell.EnvironmentText = environmentData.EnvironmentText;
            cell.ConnectApiUrlText = environmentData.Api;

            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var environmentData = _environmentViewModel?.Environments[indexPath.Row];
            SelectViewDelegate?.OnSelectedEnvironment(environmentData);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            throw new NotImplementedException();
        }

        [UIAction]
        void OnDeleteLocalData()
        {
            SelectViewDelegate.OnDeleteLocalData();
        }

        [UIAction]
        void OnSpecifiedDomainSetting()
        {
            SelectViewDelegate.OnSpecifiedDomainSetting();
        }

        public void OnDismissalWithDataDeletion(bool isDataDeleted)
        {
            if (isDataDeleted)
            {
                // ローカルデータが削除された場合
                Argc.OnDeleted.Invoke();
            }
            else
            {
                // ローカルデータが削除されなかった場合
                Argc.OnNotDeleted.Invoke();
            }
        }

        bool IEscapeResponder.OnEscape()
        {
            return true;
        }
    }
}
