using System;
using GLOW.Core.Domain.Constants.Shop;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.PageContent;
using GLOW.Scenes.PackShop.Presentation.ViewModels;
using GLOW.Scenes.Shop.Presentation.Component;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.PackShop.Presentation.Views
{
    public class PackShopView : UIView
    {
        [SerializeField] PageContentView _pageContentView;
        [SerializeField] PackShopProductListCell _cell;
        [SerializeField] UIObject _normalPackContentArea;
        [SerializeField] UIObject _dailyPackContentArea;
        [SerializeField] ChildScaler _normalPackContentChildScaler;
        [SerializeField] ChildScaler _dailyPackContentChildScaler;
        [SerializeField] ShopSectionHeaderView _normalPackSectionHeader;
        [SerializeField] ShopSectionHeaderView _dailyPackSectionHeader;

        public PageContentView PageContentView => _pageContentView;

        public PackShopProductListCell InstantiateNormalPackCell(
            PackShopProductViewModel viewModel,
            Action<PackShopProductViewModel> buyEvent,
            Action<MasterDataId> infoEvent)
        {
            var cell = Instantiate(_cell, _normalPackContentArea.RectTransform);
            cell.Setup(viewModel, buyEvent, infoEvent);
            return cell;
        }

        public PackShopProductListCell InstantiateDailyPackCell(
            PackShopProductViewModel viewModel,
            Action<PackShopProductViewModel> buyEvent,
            Action<MasterDataId> infoEvent)
        {
            var cell = Instantiate(_cell, _dailyPackContentArea.RectTransform);
            cell.Setup(viewModel, buyEvent, infoEvent);
            return cell;
        }


        public void PlayCellAppearanceAnimation()
        {
            _normalPackContentChildScaler.Play();
            _dailyPackContentChildScaler.Play();
        }

        public void SetNormalPackContentAreaVisible(bool visible)
        {
            _normalPackContentArea.IsVisible = visible;
        }

        public void SetDailyPackContentAreaVisible(bool visible)
        {
            _dailyPackContentArea.IsVisible = visible;
        }

        public void SetNormalPackSectionHeaderVisible(bool visible)
        {
            _normalPackSectionHeader.gameObject.SetActive(visible);
        }
        
        public void SetDailyPackRemainingTime(RemainingTimeSpan remainingTime)
        {
            _dailyPackSectionHeader.SetupShopSection(DisplayShopProductType.Daily, remainingTime);
        }
    }
}
