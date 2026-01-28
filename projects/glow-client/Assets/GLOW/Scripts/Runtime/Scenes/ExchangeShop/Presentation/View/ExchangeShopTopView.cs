using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.ExchangeShop.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ExchangeShop.Presentation.View
{
    public class ExchangeShopTopView : UIView
    {
        [SerializeField] UICollectionView _collectionView;
        [SerializeField] UIText _titleText;
        [Header("所持数")]
        [SerializeField] RectTransform _amountRoot;
        [SerializeField] ExchangeShopTopAmountComponent _itemAmountComponent;

        readonly List<ExchangeShopTopAmountComponent> _amountComponents = new();

        public void InitializeView(
            IUICollectionViewDelegate collectionViewDelegate,
            IUICollectionViewDataSource dataSource)
        {
            _collectionView.Delegate = collectionViewDelegate;
            _collectionView.DataSource = dataSource;
            InitializeAmountComponent();
        }

        public UICollectionViewCell GetCollectionViewCell(UIIndexPath indexPath)
        {
            return _collectionView.CellForRow(indexPath);
        }

        public void Setup(ExchangeShopTopViewModel viewmodel)
        {
            _titleText.SetText(viewmodel.Name.Value);

            SetUpAmountComponent(viewmodel.ExchangeShopTopAmountViewModels);
        }

        void InitializeAmountComponent()
        {
            foreach (var component in _amountComponents)
            {
                Destroy(component.gameObject);
            }
            _amountComponents.Clear();
        }

        void SetUpAmountComponent(IReadOnlyList<ExchangeShopTopAmountViewModel> viewmodelExchangeShopTopAmountViewModels)
        {
            foreach (var viewmodel in viewmodelExchangeShopTopAmountViewModels)
            {
                // プール対象あれば更新
                if (_amountComponents.Any(c => c.IsSameItem(viewmodel.ItemIconAssetPath)))
                {
                    var item =
                        _amountComponents.First(a => a.IsSameItem(viewmodel.ItemIconAssetPath));
                    item.UpdateAmount(viewmodel.Amount);
                    continue;
                }

                // 当該なければ新規作成
                var instance = CreateAmountComponent(viewmodel);
                _amountComponents.Add(instance);
            }
        }

        ExchangeShopTopAmountComponent CreateAmountComponent(ExchangeShopTopAmountViewModel viewModel)
        {
            var instance = Instantiate(_itemAmountComponent, _amountRoot);
            instance.Setup(viewModel);
            return instance;
        }

    }
}
