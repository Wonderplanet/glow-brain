using System;
using System.Collections.Generic;
using UIKit;
using WPFramework.Debugs.Environment.Presentation.ViewModels;
using Zenject;

namespace WPFramework.Debugs.Environment.Presentation.Views
{
    public sealed class DebugEnvironmentSpecifiedDomainViewController :
        UIViewController<DebugEnvironmentSpecifiedDomainView>,
        IUICollectionViewDelegate,
        IUICollectionViewDataSource
    {
        public class Arguments
        {
            public Action ConfirmAction { get; }
            public Action CancelAction { get; }
            public Action ResetAction { get; }

            public Arguments(Action onConfirm, Action onReset, Action onCancel)
            {
                ConfirmAction = onConfirm;
                ResetAction = onReset;
                CancelAction = onCancel;
            }
        }

        class EnvironmentDataStore
        {
            public class EnvironmentData
            {
                public string Key { get; }
                public string Value { get; set; }

                public EnvironmentData(string key, string value)
                {
                    Key = key;
                    Value = value;
                }
            }

            readonly List<EnvironmentData> _environmentDataList;

            public EnvironmentDataStore(Dictionary<string, string> propertyTable)
            {
                _environmentDataList = new List<EnvironmentData>();
                foreach (var pair in propertyTable)
                {
                    _environmentDataList.Add(new EnvironmentData(pair.Key, pair.Value));
                }
            }

            public int Count()
            {
                return _environmentDataList.Count;
            }

            public void SetValue(string key, string value)
            {
                var data = _environmentDataList.Find(d => d.Key == key);
                if (data != null)
                {
                    data.Value = value;
                }
            }

            public EnvironmentData GetValue(int index)
            {
                return _environmentDataList[index];
            }

            public EnvironmentData GetValue(string key)
            {
                return _environmentDataList.Find(d => d.Key == key);
            }
        }

        [Inject] IDebugEnvironmentSpecifiedDomainViewDelegate ViewDelegate { get; }
        [Inject] Arguments Argc { get; }

        [Inject]
        public void InjectView(DebugEnvironmentSpecifiedDomainView view)
        {
            // NOTE: デバッグ機能の場合UIViewRepositoryを経由しないため外部から直接注入する
            TempleteView = view;
        }

        EnvironmentDataStore _environmentDataStore;

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.OnViewDidLoad();

            ActualView.CollectionView.DataSource = this;
            ActualView.CollectionView.Delegate = this;
        }

        public void SetViewModel(DebugEnvironmentSpecifiedDomainViewModel viewModel)
        {
            // NOTE: partialにより定義が追加される可能性があるためリフレクションを用いて設定項目を取得する
            //       定義の追加などを自動的に追従するようにする
            var viewModelType = typeof(DebugEnvironmentSpecifiedDomainViewModel);
            var propertyTable = new Dictionary<string, string>();
            var properties = viewModelType.GetProperties();
            foreach (var prop in properties)
            {
                var value = prop.GetValue(viewModel);
                propertyTable[prop.Name] = value?.ToString();
            }

            _environmentDataStore = new EnvironmentDataStore(propertyTable);

            ActualView.CollectionView.ReloadData();
        }

        [UIAction]
        public void OnConfirm()
        {
            // NOTE: partialにより定義が追加される可能性があるためリフレクションを用いてインスタンスを設定する
            var viewModelType = typeof(DebugEnvironmentSpecifiedDomainViewModel);
            var properties = viewModelType.GetProperties();
            var viewModel = new DebugEnvironmentSpecifiedDomainViewModel();
            foreach (var prop in properties)
            {
                var data = _environmentDataStore.GetValue(prop.Name);
                prop.SetValue(viewModel, data.Value);
            }
            ViewDelegate.OnConfirm(viewModel);

            Argc.ConfirmAction?.Invoke();
            Dismiss();
        }

        [UIAction]
        public void OnCancel()
        {
            Argc.CancelAction?.Invoke();
            Dismiss();
        }

        [UIAction]
        public void OnReset()
        {
            ViewDelegate.OnReset();
            Argc.ResetAction?.Invoke();
            Dismiss();
        }

        void IUICollectionViewDelegate.DidSelectRowAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
        }

        void IUICollectionViewDelegate.AccessoryButtonTappedForRowWith(UICollectionView collectionView, UIIndexPath indexPath, object identifier)
        {
        }

        int IUICollectionViewDataSource.NumberOfItemsInSection(UICollectionView collectionView, int section)
        {
            return _environmentDataStore.Count();
        }

        UICollectionViewCell IUICollectionViewDataSource.CellForItemAtIndexPath(UICollectionView collectionView, UIIndexPath indexPath)
        {
            var cell = collectionView.DequeueReusableCell<DebugEnvironmentSpecifiedDomainCollectionCell>();
            var data = _environmentDataStore.GetValue(indexPath.Row);
            cell.NameText = data.Key;
            cell.InputFieldText = data.Value;
            cell.EndEditAction = (key, value) => _environmentDataStore.SetValue(key, value);
            return cell;
        }
    }
}
