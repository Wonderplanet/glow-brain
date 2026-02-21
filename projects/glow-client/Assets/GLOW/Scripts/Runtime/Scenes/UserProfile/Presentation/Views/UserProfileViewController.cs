using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UserProfile.Presentation.ViewModels;
using UIKit;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UserProfile.Presentation.Views
{
    public class UserProfileViewController : UIViewController<UserProfileView>, IEscapeResponder
    {
        [Inject] IUserProfileViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ActualView.OnIconTapped = ViewDelegate.OnIconTapped;

            ViewDelegate.OnViewDidLoad();
        }

        public override void ViewDidAppear()
        {
            base.ViewDidAppear();
            ViewDelegate.OnViewDidAppear();
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            ViewDelegate.OnViewDidUnload();
        }
        public override void ViewDidDisappear()
        {
            base.ViewDidDisappear();
            EscapeResponderRegistry.Unregister(this);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            UISoundEffector.Main.PlaySeEscape();
            ViewDelegate.OnCloseSelected();
            return true;
        }

        public void InitializeCollectionView()
        {
            ActualView.InitializeCollectionView();
        }

        public void SetUserName(UserName userName)
        {
            ActualView.SetUserName(userName);
        }

        public void SetUserId(UserMyId myId)
        {
            ActualView.SetUserId(myId);
        }

        public void SetCurrentAvatarIconImage(CharacterIconAssetPath assetPath)
        {
            ActualView.SetCurrentAvatarIconImage(assetPath);
        }

        public void SetupAvatarListAndReload(IReadOnlyList<UserProfileAvatarCellViewModel> viewModels, MasterDataId selectedId)
        {
            ActualView.SetupAvatarListAndReload(viewModels, selectedId);
        }
        
        public void PlayAvatarListCellAppearanceAnimation()
        {
            ActualView.PlayAvatarListCellAppearanceAnimation();
        }

        public void PlayNameEditAnimation()
        {
            ActualView.PlayNameEditAnimation();
        }

        [UIAction]
        void OnChangeNameButton()
        {
            ViewDelegate.OnChangeNameSelected();
        }

        [UIAction]
        void OnButtonClose()
        {
            ViewDelegate.OnCloseSelected();
        }
    }
}
