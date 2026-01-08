using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UserProfile.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UserProfile.Presentation.Views.Component
{
    public class UserProfileAvatarCell : UICollectionViewCell
    {
        [SerializeField] UIImage _avatarIcon;
        [SerializeField] UIObject _selectedFrame;
        [SerializeField] UIObject _noticeIcon;

        public MasterDataId MstAvaterId { get; private set; }

        public void Setup(UserProfileAvatarCellViewModel viewModel)
        {
            MstAvaterId = viewModel.Id;
            UISpriteUtil.LoadSpriteWithFade(_avatarIcon.Image, viewModel.AvatarIconAssetPath.Value);
            _noticeIcon.Hidden = !viewModel.Badge.Value;
        }

        public bool IsSelected
        {
            set => _selectedFrame.Hidden = !value;
        }

        public bool IsNotice
        {
            set => _noticeIcon.Hidden = !value;
        }
    }
}
