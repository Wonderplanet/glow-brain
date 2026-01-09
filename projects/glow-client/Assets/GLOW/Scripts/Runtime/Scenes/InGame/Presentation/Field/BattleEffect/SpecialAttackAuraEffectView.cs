using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class SpecialAttackAuraEffectView : BaseBattleEffectView
    {
        static readonly int FadeAnimationTrigger = Animator.StringToHash("fade");

        [SerializeField] Animator _animator;
        [SerializeField] AnimationClip _fadeAnimationClip;
        [SerializeField] string _attachTargetBone;

        CharacterColor _unitColor;
        float _fadeDuration;

        protected override void Awake()
        {
            base.Awake();

            _animator.enabled = false;
            _fadeDuration = _fadeAnimationClip != null ? _fadeAnimationClip.length : 0f;
        }

        public override void Destroy()
        {
            base.Destroy();

            _animator.SetTrigger(FadeAnimationTrigger);

            WaitComplete(this.GetCancellationTokenOnDestroy()).Forget();
        }

        public override BaseBattleEffectView Play()
        {
            _animator.enabled = true;
            _animator.SetBool(_unitColor.ToString(), true);

            return base.Play();
        }

        public override BaseBattleEffectView BindCharacterUnit(FieldUnitView fieldUnitView)
        {
            _unitColor = fieldUnitView.UnitColor;

            if (!string.IsNullOrEmpty(_attachTargetBone))
            {
                SkeletonAnimationFollowerFactory.BindSkeletonAnimation(gameObject,
                    fieldUnitView.SkeletonAnimation,
                    _attachTargetBone,
                    false);
            }

            return base.BindCharacterUnit(fieldUnitView);
        }

        public override BaseBattleEffectView BindSpecialUnit(FieldSpecialUnitView fieldSpecialUnitView)
        {
            _unitColor = fieldSpecialUnitView.UnitColor;

            if (!string.IsNullOrEmpty(_attachTargetBone))
            {
                SkeletonAnimationFollowerFactory.BindSkeletonAnimation(gameObject,
                    fieldSpecialUnitView.SkeletonAnimation,
                    _attachTargetBone,
                    false);
            }

            return base.BindSpecialUnit(fieldSpecialUnitView);
        }

        async UniTask WaitComplete(CancellationToken cancellationToken)
        {
            await UniTask.Delay(TimeSpan.FromSeconds(_fadeDuration), cancellationToken: cancellationToken);
            Complete();
        }
    }
}
