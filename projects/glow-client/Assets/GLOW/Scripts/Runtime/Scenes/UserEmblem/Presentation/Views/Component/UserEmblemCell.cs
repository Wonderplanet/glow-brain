using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UserEmblem.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UserEmblem.Presentation.Views.Component
{
    public class UserEmblemCell : UICollectionViewCell
    {
        [SerializeField] UIImage _emblemIconImage;
        [SerializeField] UIObject _selectedFrame;
        [SerializeField] UIObject _noticeIcon;

        public MasterDataId MstEmblemId { get; private set; }

        public void Setup(HeaderUserEmblemCellViewModel viewModel)
        {
            MstEmblemId = viewModel.Id;
            UISpriteUtil.LoadSpriteWithFade(_emblemIconImage.Image, viewModel.AssetPath.Value);
            _noticeIcon.Hidden = !viewModel.Badge.Value;
        }

        public bool IsSelected
        {
            set => _selectedFrame.Hidden = !value;
        }
    }
}
