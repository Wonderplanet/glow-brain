using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.UserProfile.Presentation.ViewModels;
using GLOW.Scenes.UserProfile.Presentation.Views.Component;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Modules;

namespace GLOW.Scenes.UserProfile.Presentation.Views
{
    public class UserProfileView : UIView
    {
        [SerializeField] UIText _userNameText;
        [SerializeField] UIText _userIdText;
        [SerializeField] UIImage _avatarIconImage;
        [SerializeField] UIObject _nameEditAnimation;
        [SerializeField] UserProfileAvatarCellList _cellList;

        public Action<MasterDataId> OnIconTapped { get; set; }

        public void SetUserName(UserName userName)
        {
            _userNameText.SetText(userName.Value);
        }

        public void SetUserId(UserMyId myId)
        {
            _userIdText.SetText("アカウントID : {0}", myId.Value);
        }

        public void SetCurrentAvatarIconImage(CharacterIconAssetPath assetPath)
        {
            UISpriteUtil.LoadSpriteWithFade(_avatarIconImage.Image, assetPath.Value);
        }

        public void InitializeCollectionView()
        {
            _cellList.InitializeCollectionView();
            _cellList.OnIconTapped = OnIconTapped;
        }

        public void SetupAvatarListAndReload(IReadOnlyList<UserProfileAvatarCellViewModel> viewModels, MasterDataId selectedId)
        {
            _cellList.SetupAndReload(viewModels, selectedId);
        }
        
        public void PlayAvatarListCellAppearanceAnimation()
        {
            _cellList.PlayCellAppearanceAnimation();
        }

        public void PlayNameEditAnimation()
        {
            _nameEditAnimation.Hidden = false;
        }
    }
}
