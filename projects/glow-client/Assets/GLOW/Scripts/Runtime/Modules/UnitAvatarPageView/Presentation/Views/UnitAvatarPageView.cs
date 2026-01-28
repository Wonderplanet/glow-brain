using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.UnitEnhance.Presentation.Views.Components;
using UIKit;
using UnityEngine;
using UnityEngine.EventSystems;

namespace GLOW.Modules.UnitAvatarPageView.Presentation.Views
{
    public class UnitAvatarPageView : UIView,
        IPointerClickHandler
    {
        [SerializeField] UISpineWithOutlineAvatar _avatar;
        [SerializeField] UnitEnhanceLevelUpAvatarAnimationComponent _levelUpAvatarAnimationComponent;
        [SerializeField] AvatarFooterShadowComponent _footerShadowComponent;

        public UISpineWithOutlineAvatar Avatar => _avatar;
        public AvatarFooterShadowComponent FooterShadow => _footerShadowComponent;

        bool _isEnemy;
        bool _isFlip;
        CharacterUnitAnimation _currentAnimation = CharacterUnitAnimation.Wait;

        protected override void OnEnable()
        {
            base.OnEnable();
            _levelUpAvatarAnimationComponent.EndAnimation();
        }

        public void SetFlip(bool flip)
        {
            _avatar.Flip = flip;
            var oldFlip = _isFlip;
            _isFlip = flip;
            if (oldFlip != flip)
            {
                PlayAnimation(_currentAnimation, CharacterUnitAnimation.Empty);
            }
        }

        public void PlayWaitAnimation()
        {
            PlayAnimation(CharacterUnitAnimation.Wait, CharacterUnitAnimation.Empty);
        }

        public void PlayLevelUpAnimation()
        {
            _levelUpAvatarAnimationComponent.PlayAnimation();
            // _avatar.Animate(CharacterUnitAnimation.WaitJoy.Name, CharacterUnitAnimation.Wait.Name);
            PlayAnimation(CharacterUnitAnimation.WaitJoy, CharacterUnitAnimation.Wait);
        }

        public void PlayMoveAnimation()
        {
            PlayAnimation(CharacterUnitAnimation.Move, CharacterUnitAnimation.Empty);
        }

        void PlayAnimation(CharacterUnitAnimation unitAnimation, CharacterUnitAnimation nextAnimation)
        {
            _currentAnimation = unitAnimation;

            var playAnimation = GetMirrorAnimation(unitAnimation);

            if (playAnimation.Name != _avatar.GetCurrentAnimationStateName())
            {
                if (nextAnimation.IsEmpty())
                {
                    _avatar.Animate(playAnimation.Name);
                }
                else
                {
                    _avatar.Animate(playAnimation.Name, nextAnimation.Name);
                }
            }
        }

        CharacterUnitAnimation GetMirrorAnimation(CharacterUnitAnimation unitAnimation)
        {
            return unitAnimation.Type switch
            {
                UnitAnimationType.Wait =>
                    _isFlip && _avatar.IsFindAnimation(CharacterUnitAnimation.MirrorWait.Name)
                        ? CharacterUnitAnimation.MirrorWait
                        : CharacterUnitAnimation.Wait,
                UnitAnimationType.Move =>
                    _isFlip && _avatar.IsFindAnimation(CharacterUnitAnimation.MirrorMove.Name)
                        ? CharacterUnitAnimation.MirrorMove
                        : CharacterUnitAnimation.Move,
                UnitAnimationType.Attack =>
                    _isFlip && _avatar.IsFindAnimation(CharacterUnitAnimation.MirrorAttack.Name)
                        ? CharacterUnitAnimation.MirrorAttack
                        : CharacterUnitAnimation.Attack,
                UnitAnimationType.WaitJoy =>
                    _isFlip && _avatar.IsFindAnimation(CharacterUnitAnimation.MirrorWaitJoy.Name)
                        ? CharacterUnitAnimation.MirrorWaitJoy
                        : CharacterUnitAnimation.WaitJoy,
                _ => CharacterUnitAnimation.Wait
            };
        }

        void IPointerClickHandler.OnPointerClick(PointerEventData eventData)
        {
            var currentStateName = _avatar.GetCurrentAnimationStateName();

            if (currentStateName == CharacterUnitAnimation.Wait.Name)
            {
                _avatar.Animate(CharacterUnitAnimation.Attack.Name, CharacterUnitAnimation.Wait.Name);
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }
            else if(currentStateName == CharacterUnitAnimation.MirrorWait.Name)
            {
                _avatar.Animate(CharacterUnitAnimation.MirrorAttack.Name, CharacterUnitAnimation.MirrorWait.Name);
                SoundEffectPlayer.Play(SoundEffectId.SSE_000_002);
            }
        }
    }
}
