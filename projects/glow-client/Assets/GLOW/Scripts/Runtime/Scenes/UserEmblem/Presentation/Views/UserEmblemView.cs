using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Core.Presentation.Modules;
using GLOW.Scenes.UserEmblem.Presentation.ViewModels;
using GLOW.Scenes.UserEmblem.Presentation.Views.Component;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UserEmblem.Presentation.Views
{
    public class UserEmblemView : UIView
    {
        [SerializeField] UIImage _emblemIconImage;
        [SerializeField] UIText _emblemDescriptionText;
        [SerializeField] UserEmblemCellList _userEmblemCellList;
        [SerializeField] UIToggleableComponentGroup _tabButtonGroup;
        [SerializeField] UIObject _seriesNoticeIcon;
        [SerializeField] UIObject _eventNoticeIcon;

        public Action<MasterDataId> OnIconTapped { get; set; }

        public void SetCurrentEmblem(HeaderUserEmblemCellViewModel viewModel, bool isEmpty)
        {
            if (isEmpty)
            {
                _emblemIconImage.Hidden = true;
                _emblemDescriptionText.SetText("獲得したエンブレムを\nタップすることで設定できます");
            }
            else
            {
                _emblemIconImage.Hidden = false;
                SpriteLoaderUtil.Clear(_emblemIconImage.Image);
                UISpriteUtil.LoadSpriteWithFade(_emblemIconImage.Image, viewModel.AssetPath.Value);
                _emblemDescriptionText.SetText(viewModel.Description.Value);
            }
        }

        public void Setup()
        {
            _userEmblemCellList.Setup();
        }

        public void EmblemListReload(IReadOnlyList<HeaderUserEmblemCellViewModel> viewModels, MasterDataId selectedId)
        {
            _userEmblemCellList.OnIconTapped = OnIconTapped;
            _userEmblemCellList.Reload(viewModels, selectedId);
        }

        public void PlayEmblemListCellAppearanceAnimation()
        {
            _userEmblemCellList.PlayCellAppearanceAnimation();
        }

        public void SetTabButtonSelected(EmblemType tabType, bool seriesNotice, bool eventNotice)
        {
            _tabButtonGroup.SetToggleOn(tabType == EmblemType.Series ? "Series" : "Event");

            _seriesNoticeIcon.Hidden = !seriesNotice;
            _eventNoticeIcon.Hidden = !eventNotice;
        }
    }
}
