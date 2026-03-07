using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.HomeMainKomaSettingUnitSelect.Presentation
{
    public class HomeMainKomaSettingUnitSelectCell : UICollectionViewCell
    {
        [SerializeField] UIImage _avatarIcon;
        [SerializeField] UIObject _selectedFrame;
        [SerializeField] UIObject _otherSelectedGrayout;


        public MasterDataId MstUnitId { get; private set; }

        public void Setup(HomeMainKomaSettingUnitSelectItemViewModel viewModel)
        {
            MstUnitId = viewModel.MstUnitId;
            UISpriteUtil.LoadSpriteWithFade(_avatarIcon.Image, viewModel.AssetPath.Value);
            _selectedFrame.IsVisible = viewModel.IsSelected();
            _otherSelectedGrayout.IsVisible = viewModel.IsGrayout();
        }

    }
}
