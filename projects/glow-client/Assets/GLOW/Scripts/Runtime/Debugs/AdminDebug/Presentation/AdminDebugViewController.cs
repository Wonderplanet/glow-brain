using System.Globalization;
using GLOW.Debugs.Command.Domains.UseCase;
using GLOW.Debugs.Command.Presentations.ViewModels;
using UIKit;
using Zenject;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public sealed class AdminDebugViewController : UIViewController<AdminDebugView>, IUICollectionViewDataSource, IUICollectionViewDelegate
    {
        [Inject] IAdminDebugViewDelegate ViewDelegate { get; }

        const string TimeFormat = "yyyy-MM-dd HH:mm:ss zzz";
        AdminDebugViewModel _viewModel;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad(this);

            ActualView.CommandCollectionView.DataSource = this;
            ActualView.CommandCollectionView.Delegate = this;
        }

        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            ViewDelegate.OnViewWillAppear();
        }

        public void SetViewModel(AdminDebugViewModel viewModel)
        {
            _viewModel = viewModel;
            ActualView.CommandCollectionView.ReloadData();
        }

        public void SetEnvName(DebugCommandEnvName envName)
        {
            ActualView.EnvNameText.text = envName.Value;
        }

        public void SetTime(DebugCommandTimeViewModel viewModel)
        {
            ActualView.ApplicationTimeText.text = viewModel.CurrentTime.ToLocalTime().ToString(TimeFormat, CultureInfo.InvariantCulture);
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _viewModel?.CommandList?.Length ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<AdminDebugViewCell>();
            var command = _viewModel?.CommandList[indexPath.Row];
            if (command == null)
            {
                return cell;
            }

            cell.NameText = command.Name;
            cell.DescriptionText = command.Description;
            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var command = _viewModel.CommandList[indexPath.Row];
            ViewDelegate?.OnSelectCommand(command);
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
            throw new System.NotImplementedException();
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            Dismiss();
        }
    }
}
