using System.Collections.Generic;
using System.Globalization;
using UIKit;
using UnityEngine.UI;
using WonderPlanet.ToastNotifier;
using Zenject;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public sealed class AdminDebugInputViewController : UIViewController<AdminDebugInputView>,
        IUICollectionViewDataSource,
        IUICollectionViewDelegate
    {
        [Inject] IAdminDebugInputViewDelegate ViewDelegate { get; }

        AdminDebugInputViewModel _viewModel;
        readonly Dictionary<string, string> _inputValues = new();
        bool _isViewLoaded;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad(this);

            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
            _isViewLoaded = true;
        }

        public void SetViewModel(AdminDebugInputViewModel viewModel)
        {
            _viewModel = viewModel;
            _inputValues.Clear();

            foreach (var cell in _viewModel.CellViewModels)
            {
                _inputValues[cell.Name] = string.Empty;
            }

            if (_isViewLoaded) 
            {
                ActualView.CollectionView.ReloadData();
            }
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(
            UICollectionView collectionView,
            int section)
        {
            return _viewModel?.CellViewModels?.Length ?? 0;
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<AdminDebugInputViewCell>();
            var cellViewModel = _viewModel?.CellViewModels[indexPath.Row];
            if (cellViewModel == null) return cell;

            cell.NameText = cellViewModel.Name;
            cell.DescriptionText = cellViewModel.Description ?? string.Empty;

            var placeholder = cellViewModel.Type ?? "string";
            if (cellViewModel.Min.HasValue || cellViewModel.Max.HasValue)
            {
                var min = cellViewModel.Min?.ToString(CultureInfo.InvariantCulture) ?? "";
                var max = cellViewModel.Max?.ToString(CultureInfo.InvariantCulture) ?? "";
                placeholder += $" ({min}~{max})";
            }
            cell.PlaceholderText = placeholder;

            cell.InputFieldContentType = cellViewModel.Type == "integer"
                ? InputField.ContentType.IntegerNumber
                : InputField.ContentType.Standard;

            cell.InputFieldText = _inputValues.GetValueOrDefault(cellViewModel.Name, string.Empty);

            var paramName = cellViewModel.Name;
            cell.OnInputFieldValueChanged = value => _inputValues[paramName] = value;

            return cell;
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(
            UICollectionView collectionView,
            UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(
            UICollectionView collectionView,
            UIIndexPath indexPath,
            object identifier)
        {
        }

        [UIAction]
        public void OnSubmitButtonTapped()
        {
            var parameters = new Dictionary<string, object>();
            foreach (var cellViewModel in _viewModel.CellViewModels)
            {
                var value = _inputValues.GetValueOrDefault(cellViewModel.Name, string.Empty);
                var invalidMessage = GetInvalidMessage(cellViewModel, value);

                if (!string.IsNullOrEmpty(invalidMessage))
                {
                    Toast.MakeText(invalidMessage)?.Show();
                    return;
                }

                if (cellViewModel.Type == "integer")
                {
                    parameters[cellViewModel.Name] = int.Parse(value, CultureInfo.InvariantCulture);
                }
                else
                {
                    parameters[cellViewModel.Name] = value;
                }
            }

            ViewDelegate.OnSubmit(_viewModel.Command, parameters);
        }

        string GetInvalidMessage(AdminDebugInputCellViewModel cellViewModel, string value)
        {           
                if (string.IsNullOrEmpty(value))
                {
                    return $"{cellViewModel.Name}を入力してください";
                }

                if (cellViewModel.Type == "integer")
                {
                    if (!int.TryParse(value, out var intValue))
                    {
                        return $"{cellViewModel.Name}は整数で入力してください";
                    }
                    if (cellViewModel.Min.HasValue && intValue < cellViewModel.Min.Value)
                    {
                        return $"{cellViewModel.Name}は{cellViewModel.Min.Value}以上で入力してください" ;
                    }
                    if (cellViewModel.Max.HasValue && intValue > cellViewModel.Max.Value)
                    {
                        return $"{cellViewModel.Name}は{cellViewModel.Max.Value}以下で入力してください" ;
                    }
                }

            return "";
        }

        [UIAction]
        public void OnCloseButtonTapped()
        {
            Dismiss();
        }
    }
}
