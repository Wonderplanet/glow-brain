using System;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using Spine.Unity;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    /// <summary> SD(Spine)でのゲート表示 /// </summary>
    public class OutpostSDSpriteView : OutpostSpriteView
    {
        [SerializeField] SkeletonAnimation _skeletonAnimation;
        [SerializeField] FieldUnitShadowTrace _fieldUnitShadowTrace;

        public SkeletonAnimation SkeletonAnimation => _skeletonAnimation;

        OutpostSDAnimation _currentAnimation = OutpostSDAnimation.Empty;
        OutpostSDAnimationType _waitAnimationType = OutpostSDAnimationType.Wait; // 待機時のアニメーション。被弾時に更新させる

        public override void Initialize(
            GameObject spriteRoot,
            BattleSide battleSide,
            OutpostViewInfo viewInfo,
            PageComponent pageComponent)
        {
            base.Initialize(spriteRoot, battleSide, viewInfo, pageComponent);

            _skeletonAnimation.Skeleton.ScaleX = battleSide == BattleSide.Player ? 1f : -1f;
            _waitAnimationType = OutpostSDAnimationType.Wait;

            PlayAnimation(OutpostSDAnimationType.Wait, OutpostSDAnimationType.Empty, ignoresPriority:false);
            _fieldUnitShadowTrace.RegisterSkeletonAnimation(SkeletonAnimation);
            _fieldUnitShadowTrace.SetupShadowColor(CharacterColor.Colorless);
        }

        /// <summary> Animationごとの優先度をもとに再生。現在再生中のAnimationと同じの場合スキップ /// </summary>
        public override void PlayAnimation(
            OutpostSDAnimationType animationType,
            OutpostSDAnimationType nextAnimationType,
            bool ignoresPriority,
            Action onCompleted = null)
        {
            var playAnimation = GetAnimation(animationType);
            if (!HasAnimation(playAnimation))
            {
                return;
            }

            if (_currentAnimation != playAnimation &&
                (ignoresPriority || _currentAnimation.Priority < playAnimation.Priority))
            {
                _currentAnimation = playAnimation;

                var trackEntry = _skeletonAnimation.AnimationState.SetAnimation(
                    0,
                    playAnimation.Name,
                    playAnimation.IsLoop);

                trackEntry.Complete += _ =>
                {
                    onCompleted?.Invoke();
                    if (!playAnimation.IsLoop && nextAnimationType != OutpostSDAnimationType.Empty)
                    {
                        _currentAnimation = OutpostSDAnimation.Empty;
                        PlayAnimation(nextAnimationType, OutpostSDAnimationType.Empty, ignoresPriority:false);
                    }
                };
            }
        }

        public override void OnSummonUnit()
        {
            base.OnSummonUnit();
            PlayAnimation(OutpostSDAnimationType.Attack, _waitAnimationType, ignoresPriority:false);
        }

        public override void OnBreakDown(FieldViewCoordV2 fieldViewPos, Vector3 breakDownEffectOffset)
        {
            PlayAnimation(
                OutpostSDAnimationType.Death,
                _waitAnimationType,
                ignoresPriority:false,
                () => gameObject.SetActive(false));

            SoundEffectPlayer.Play(SoundEffectId.SSE_051_065);
        }

        public override void OnRecover()
        {
            gameObject.SetActive(true);
            _waitAnimationType = OutpostSDAnimationType.Wait;
            PlayAnimation(_waitAnimationType, OutpostSDAnimationType.Empty, ignoresPriority:true);
        }

        public override void OnHitAttacks(bool isDangerHp)
        {
            base.OnHitAttacks(isDangerHp);

            _waitAnimationType = HasAnimation(OutpostSDAnimationType.Pinch) && isDangerHp
                ? OutpostSDAnimationType.Pinch
                : OutpostSDAnimationType.Wait;

            PlayAnimation(OutpostSDAnimationType.Damage, _waitAnimationType, ignoresPriority:false);
        }

        OutpostSDAnimation GetAnimation(OutpostSDAnimationType animationType)
        {
            bool isMirror = this.BattleSide != BattleSide.Player;
            switch (animationType)
            {
                case OutpostSDAnimationType.Attack:
                    return isMirror && HasAnimation(OutpostSDAnimation.MirrorAttack)
                        ? OutpostSDAnimation.MirrorAttack
                        : OutpostSDAnimation.Attack;

                case OutpostSDAnimationType.Beam:
                    return isMirror && HasAnimation(OutpostSDAnimation.MirrorBeam)
                        ? OutpostSDAnimation.MirrorBeam
                        : OutpostSDAnimation.Beam;

                case OutpostSDAnimationType.Damage:
                    return isMirror && HasAnimation(OutpostSDAnimation.MirrorDamage)
                        ? OutpostSDAnimation.MirrorDamage
                        : OutpostSDAnimation.Damage;

                case OutpostSDAnimationType.Death:
                    return isMirror && HasAnimation(OutpostSDAnimation.MirrorDeath)
                        ? OutpostSDAnimation.MirrorDeath
                        : OutpostSDAnimation.Death;

                case OutpostSDAnimationType.Wait:
                    return isMirror && HasAnimation(OutpostSDAnimation.MirrorWait)
                        ? OutpostSDAnimation.MirrorWait
                        : OutpostSDAnimation.Wait;

                case OutpostSDAnimationType.Pinch:
                    return isMirror && HasAnimation(OutpostSDAnimation.MirrorPinch)
                        ? OutpostSDAnimation.MirrorPinch
                        : OutpostSDAnimation.Pinch;

                default:
                    return OutpostSDAnimation.Empty;
            }
        }

        bool HasAnimation(OutpostSDAnimationType animationType)
        {
            var checkAnimation = GetAnimation(animationType);
            return HasAnimation(checkAnimation);
        }

        bool HasAnimation(OutpostSDAnimation checkAnimation)
        {
            return _skeletonAnimation.Skeleton.Data.Animations.Exists(x => x.Name == checkAnimation.Name);
        }
    }
}
