using System;
using GLOW.Scenes.Community.Presentation.Component;
using GLOW.Scenes.Community.Presentation.ViewModel;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.Community.Presentation.View
{
    /// <summary>
    /// 121_メニュー
    /// 　121-1メニュー（ホーム画面）
    /// 　121-5_メディア
    /// 　　121-5-1_メディア
    /// </summary>
    public class CommunityMenuView : UIView
    {
        [SerializeField] CommunityMenuListComponent _jumbleRushOfficialSiteCell;
        [SerializeField] CommunityMenuListComponent _jumbleRushOfficialXCell;
        [SerializeField] CommunityMenuListComponent _jumpPlusOfficialSiteCell;
        [SerializeField] CommunityMenuListComponent _jumpPlusLinkCell;
        [SerializeField] CommunityMenuListComponent _jumpPlusOfficialXCell;
        
        public void SetCommunityMenuListComponents(CommunityMenuViewModel viewModel, Action<CommunityMenuCellViewModel> onCommunityBannerSelected)
        {

            _jumbleRushOfficialSiteCell.SetBannerButton(
                viewModel.JumbleRushOfficialSiteCellViewModel,
                onCommunityBannerSelected);
            
            _jumbleRushOfficialXCell.SetBannerButton(
                viewModel.JumbleRushOfficialXCellViewModel,
                onCommunityBannerSelected);
            
            _jumpPlusOfficialSiteCell.SetBannerButton(
                viewModel.JumpPlusOfficialSiteCellViewModel,
                onCommunityBannerSelected);
            
            _jumpPlusLinkCell.SetBannerButton(
                viewModel.JumpPlusLinkCellViewModel,
                onCommunityBannerSelected);
            
            _jumpPlusOfficialXCell.SetBannerButton(
                viewModel.JumpPlusOfficialXCellViewModel,
                onCommunityBannerSelected);
        }
    }
}