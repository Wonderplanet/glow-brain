using System;
using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.UnitDetail.Presentation.ViewModels;
using GLOW.Scenes.UnitEnhance.Presentation.ViewModels;
using UIKit;
using WonderPlanet.UniTaskSupporter;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.UnitDetail.Presentation.Views
{
    public interface IUnitDetailIViewController
    {
        void Setup(UnitDetailViewModel viewModel);
        void SetupActiveAbilityTab(bool isActive);
        void SetUnitDetail(UnitEnhanceUnitDetailViewModel viewModel);
        void SetSpecialAttack(UnitEnhanceSpecialAttackViewModel viewModel);
        void SetAbility(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModel);
        void SetStatus(UnitEnhanceUnitStatusViewModel viewModel);
        void PresentModally(UIViewController viewController);
    }

    public class UnitDetailViewController : 
        UIViewController<UnitDetailView>, 
        IUnitDetailIViewController,
        IEscapeResponder
    {
        public record Argument(MasterDataId MstUnitId, MaxStatusFlag IsMaxStatus)
        {
            public static Argument CreateMaxStatus(MasterDataId mstUnitId)
            {
                return new Argument(mstUnitId, MaxStatusFlag.True);
            }

            public static Argument CreateMinStatus(MasterDataId mstUnitId)
            {
                return new Argument(mstUnitId, MaxStatusFlag.False);
            }
        }

        [Inject] IUnitDetailViewDelegate ViewDelegate { get; }
        [Inject] IUnitImageLoader UnitImageLoader { get; }
        [Inject] IUnitImageContainer UnitImageContainer { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        public Action OnClose { get; set; }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();
            ViewDelegate.ViewDidLoad();
        }
        
        public override void ViewWillAppear(bool animated)
        {
            base.ViewWillAppear(animated);
            EscapeResponderRegistry.Unregister(this);
            EscapeResponderRegistry.Register(this);
        }
        
        public override void ViewDidUnload()
        {
            base.ViewDidUnload();
            EscapeResponderRegistry.Unregister(this);
        }

        public override void ViewWillDisappear(bool animated)
        {
            base.ViewWillDisappear(true);
            OnClose?.Invoke();
        }

        public void Setup(UnitDetailViewModel viewModel)
        {
            SetupUnitInfo(viewModel.UnitInfo);
            ActualView.SetLevelUp(viewModel.LevelUpTab);
            ActualView.SetActiveMaxStatusText(viewModel.MaxStatusFlag);
        }

        void SetupUnitInfo(UnitEnhanceUnitInfoViewModel viewModel)
        {
            ActualView.AvatarShadow.Setup(viewModel.Color);
            DoAsync.Invoke(ActualView, async cancellationToken =>
            {
                await UnitImageLoader.Load(cancellationToken, viewModel.UnitImageAssetPath);
                var prefab = UnitImageContainer.Get(viewModel.UnitImageAssetPath);
                var characterImage = prefab.GetComponent<UnitImage>();
                var skeletonDataAsset = characterImage.SkeletonAnimation.skeletonDataAsset;
                var avatarScale = characterImage.SkeletonScale;
                ActualView.Avatar.SetSkeleton(skeletonDataAsset);
                ActualView.Avatar.SetAvatarScale(avatarScale);
                ActualView.Avatar.Animate(CharacterUnitAnimation.Wait.Name);
            });

            ActualView.UnitInfo.Setup(viewModel);
            //ActualView.SpecialAttackDetail.Setup(viewModel.SpecialAttack);
        }

        public void SetupActiveAbilityTab(bool isActive)
        {
            ActualView.SetupActiveAbilityTab(isActive);
        }

        public void SetUnitDetail(UnitEnhanceUnitDetailViewModel viewModel)
        {
            ActualView.SetUnitDetail(viewModel);
        }

        public void SetSpecialAttack(UnitEnhanceSpecialAttackViewModel viewModel)
        {
            ActualView.SetSpecialAttack(viewModel);
        }

        public void SetAbility(IReadOnlyList<UnitEnhanceAbilityViewModel> viewModel)
        {
            ActualView.SetAbility(viewModel);
        }

        public void SetStatus(UnitEnhanceUnitStatusViewModel viewModel)
        {
            ActualView.SetStatus(viewModel);
        }

        public void PresentModally(UIViewController viewController)
        {
            base.PresentModally(viewController);
        }

        bool IEscapeResponder.OnEscape()
        {
            if (ActualView.Hidden) return false;

            SoundEffectPlayer.Play(SoundEffectId.SSE_000_003);
            ViewDelegate.OnBackButtonTapped();
            return true;
        }

        [UIAction]
        protected void OnBackButtonTapped()
        {
            ViewDelegate.OnBackButtonTapped();
        }


        [UIAction]
        protected void OnSpecialAttackDetailButtonTapped()
        {
            ViewDelegate.OnSpecialAttackDetailButtonTapped();
        }

        [UIAction]
        protected void OnPlayAttackAnimationButtonTapped()
        {
            ActualView.PlayAttackAnimation();
        }

        [UIAction]
        protected void OnDetailTabButtonTapped()
        {
            ViewDelegate.OnDetailTabButtonTapped();
        }

        [UIAction]
        protected void OnSpecialAttackTabButtonTapped()
        {
            ViewDelegate.OnSpecialAttackTabButtonTapped();
        }

        [UIAction]
        protected void OnAbilityTabButtonTapped()
        {
            ViewDelegate.OnAbilityTabButtonTapped();
        }

        [UIAction]
        protected void OnStatusTabButtonTapped()
        {
            ViewDelegate.OnStatusTabButtonTapped();
        }
    }
}
