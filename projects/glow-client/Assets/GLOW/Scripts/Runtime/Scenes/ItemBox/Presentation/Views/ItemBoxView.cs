using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ItemBox.Domain.ValueObjects;
using GLOW.Scenes.ItemBox.Presentation.Components;
using GLOW.Scenes.ItemBox.Presentation.ViewModels;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.ItemBox.Presentation.Views
{
    public sealed class ItemBoxView : UIView
    {
        [Header("ItemBoxIconList")]
        [SerializeField] ItemBoxIconList _itemBoxIconList;
        [SerializeField] ItemBoxIconList _itemBoxEnchanceList;
        [SerializeField] ItemBoxIconList _itemBoxIconListFragmentBox;
        [Header("タブ")]
        [SerializeField] UIToggleableComponentGroup _itemGroupTab;

        public Action<MasterDataId> OnItemIconTapped { get; set; }

        public void InitializeItemList()
        {
            _itemBoxIconList.InitializeItemList();
            _itemBoxEnchanceList.InitializeItemList();
            _itemBoxIconListFragmentBox.InitializeItemList();
        }

        public void SetupItemListAndReload(
            ItemBoxTabType itemBoxTabType,
            ItemBoxIconListViewModel viewModel)
        {
            //タブ設定
            _itemGroupTab.SetToggleOn(itemBoxTabType.ToString());

            // タブ表示
            _itemBoxIconList.gameObject.SetActive(itemBoxTabType == ItemBoxTabType.Item);
            _itemBoxEnchanceList.gameObject.SetActive(itemBoxTabType == ItemBoxTabType.Enhance);
            _itemBoxIconListFragmentBox.gameObject.SetActive(itemBoxTabType == ItemBoxTabType.CharacterFragment);

            // 各種登録
            _itemBoxIconList.OnItemIconTapped = OnItemIconTapped;
            _itemBoxIconList.SetupAndReload(viewModel.IconViewModels);

            _itemBoxEnchanceList.OnItemIconTapped = OnItemIconTapped;
            _itemBoxEnchanceList.SetupAndReload(viewModel.IconViewModels);

            _itemBoxIconListFragmentBox.OnItemIconTapped = OnItemIconTapped;
            _itemBoxIconListFragmentBox.SetupAndReload(viewModel.IconViewModels);
        }

        public void PlayCellAppearanceAnimation(ItemBoxTabType itemBoxTabType)
        {
            switch (itemBoxTabType)
            {
                case ItemBoxTabType.Item:
                    _itemBoxIconList.PlayCellAppearanceAnimation();
                    break;
                case ItemBoxTabType.Enhance:
                    _itemBoxEnchanceList.PlayCellAppearanceAnimation();
                    break;
                case ItemBoxTabType.CharacterFragment:
                    _itemBoxIconListFragmentBox.PlayCellAppearanceAnimation();
                    break;
            }
        }
    }
}
