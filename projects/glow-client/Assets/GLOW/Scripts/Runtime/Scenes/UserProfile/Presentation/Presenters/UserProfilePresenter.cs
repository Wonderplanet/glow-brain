using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Presentation.Interface;
using GLOW.Scenes.UserNameEdit.Presentation.Views;
using GLOW.Scenes.UserProfile.Domain.Models;
using GLOW.Scenes.UserProfile.Domain.UseCases;
using GLOW.Scenes.UserProfile.Presentation.ViewModels;
using GLOW.Scenes.UserProfile.Presentation.Views;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Modules.Log;
using WPFramework.Presentation.InteractionControls;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UserProfile.Presentation.Presenters
{
    public class UserProfilePresenter : IUserProfileViewDelegate
    {
        [Inject] UserProfileViewController ViewController { get; }
        [Inject] GetUserProfileModelUseCase GetUserProfileModelUseCase { get; }
        [Inject] ApplyUserAvatarUseCase ApplyUserAvatarUseCase { get; }
        [Inject] GetUserProfileAvatarBadgeUseCase GetUserProfileAvatarBadgeUseCase { get; }
        [Inject] UpdateUserProfileAvatarBadgeUseCase UpdateUserProfileAvatarBadgeUseCase { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IScreenInteractionControl ScreenInteractionControl { get; }
        [Inject] IHomeHeaderDelegate HomeHeaderDelegate { get; }

        MasterDataId _selectedAvatarId;
        List<MasterDataId> _viewedAvatarIds;

        public void OnViewDidLoad()
        {
            ApplicationLog.Log(nameof(UserProfilePresenter), nameof(IUserProfileViewDelegate.OnViewDidLoad));

            var model = GetUserProfileModelUseCase.GetUserProfileModel();
            var viewModel = ConvertToViewModel(model);
            _viewedAvatarIds = GetUserProfileAvatarBadgeUseCase.GetUserProfileAvatarBadge();

            _selectedAvatarId = model.CurrentAvatarIcon.Id;
            ViewController.InitializeCollectionView();
            ViewController.SetCurrentAvatarIconImage(viewModel.CurrentAvatarIcon.AvatarIconAssetPath);
            ViewController.SetUserName(viewModel.Name);
            ViewController.SetUserId(viewModel.MyId);
            ViewController.SetupAvatarListAndReload(viewModel.AvatarIconList, _selectedAvatarId);
        }
        
        public void OnViewDidAppear()
        {
            ApplicationLog.Log(nameof(UserProfilePresenter), nameof(IUserProfileViewDelegate.OnViewDidAppear));
            ViewController.PlayAvatarListCellAppearanceAnimation();
        }

        public void OnViewDidUnload()
        {
            ApplicationLog.Log(nameof(UserProfilePresenter), nameof(IUserProfileViewDelegate.OnViewDidUnload));
        }

        public void OnChangeNameSelected()
        {
            var controller = ViewFactory.Create<UserNameEditDialogViewController>();
            controller.OnConfirmed = ChangeNameEnd;
            ViewController.PresentModally(controller);
        }

        public void OnCloseSelected()
        {
            DoAsync.Invoke(ViewController.View, ScreenInteractionControl, async cancellationToken =>
            {
                await ApplyUserAvatarUseCase.ApplyUserAvatar(cancellationToken, _selectedAvatarId);
                HomeHeaderDelegate.UpdateStatus();
                HomeHeaderDelegate.UpdateBadgeStatus();
                ViewController.Dismiss();
            });
        }

        public void OnIconTapped(MasterDataId avatarId)
        {
            if (!_viewedAvatarIds.Contains(avatarId))
            {
                _viewedAvatarIds.Add(avatarId);
                UpdateUserProfileAvatarBadgeUseCase.UpdateUserProfileAvatarBadge(_viewedAvatarIds);
            }

            var model = GetUserProfileModelUseCase.GetUserProfileModel();
            var viewModel = ConvertToViewModel(model);

            _selectedAvatarId = avatarId;
            ViewController.SetCurrentAvatarIconImage(viewModel.AvatarIconList.First(avatar => avatar.Id == avatarId).AvatarIconAssetPath);
            ViewController.SetupAvatarListAndReload(viewModel.AvatarIconList, _selectedAvatarId);
        }

        void UpdateUserProfile()
        {
            var model = GetUserProfileModelUseCase.GetUserProfileModel();
            var viewModel = ConvertToViewModel(model);

            ViewController.SetCurrentAvatarIconImage(viewModel.AvatarIconList.First(avatar => avatar.Id == _selectedAvatarId).AvatarIconAssetPath);
            ViewController.SetUserName(viewModel.Name);
            ViewController.SetUserId(viewModel.MyId);
            ViewController.SetupAvatarListAndReload(viewModel.AvatarIconList, _selectedAvatarId);
        }

        void ChangeNameEnd()
        {
            UpdateUserProfile();
            ViewController.PlayNameEditAnimation();
        }

        UserProfileViewModel ConvertToViewModel(UserProfileModel model)
        {
            var currentAvatarIcon = new UserProfileAvatarCellViewModel(
                model.CurrentAvatarIcon.Id,
                model.CurrentAvatarIcon.AvatarIconAssetPath,
                model.CurrentAvatarIcon.Badge
            );

            var avatarList = model.AvatarIconList.Select(avatar => new UserProfileAvatarCellViewModel(
                avatar.Id,
                avatar.AvatarIconAssetPath,
                avatar.Badge
            )).ToList();

            return new UserProfileViewModel(
                model.Name,
                model.MyId,
                currentAvatarIcon,
                avatarList
            );
        }
    }
}
