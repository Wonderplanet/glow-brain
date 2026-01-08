using GLOW.Core.Domain.Extensions;
using GLOW.Core.Presentation.Components;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.PartyFormation.Presentation.ViewModels;
using GLOW.Scenes.UnitList.Domain.Constants;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Scenes.PartyFormation.Presentation.Views
{
    public class PartyFormationPartyMemberAvatarComponent : MonoBehaviour
    {
        [SerializeField] UISpineWithOutlineAvatar _spineAvatar;
        [SerializeField] AvatarFooterShadowComponent _footerShadowComponent;
        [SerializeField] PartyFormationPartyMemberStatusComponent _statusComponent;

        void Awake()
        {
            _spineAvatar.SetRaycastTarget(false);
            SetEmpty();
        }

        public void SetEmpty()
        {
            _statusComponent.IsVisible = false;
            HiddenAvatar(true);
        }

        public void Setup(PartyFormationPartyMemberViewModel viewModel)
        {
            _statusComponent.IsVisible = true;
            _statusComponent.Setup(viewModel);
            _footerShadowComponent.Setup(viewModel.Color);
        }

        /// <summary>
        /// データはリセットせず、表示のみを非表示にする
        /// </summary>
        /// <param name="isHidden"></param>
        public void HiddenAvatar(bool isHidden)
        {
            _spineAvatar.Hidden = isHidden;
            _footerShadowComponent.Hidden = isHidden;
        }

        public void SetupAvatar(SkeletonDataAsset skeleton, Vector3 avatarScale)
        {
            HiddenAvatar(false);
            _spineAvatar.SetAvatarScale(avatarScale);

            _spineAvatar.SetSkeleton(skeleton);
            _spineAvatar.Animate(CharacterUnitAnimation.Wait.Name);
        }

        public void SetStatusCanvasEnabled(bool isEnabled)
        {
            _statusComponent.SetStatusCanvasEnabled(isEnabled);
        }
    }
}
