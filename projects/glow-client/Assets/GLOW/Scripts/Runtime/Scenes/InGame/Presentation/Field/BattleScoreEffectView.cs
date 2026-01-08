using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class BattleScoreEffectView : BaseBattleEffectView
    {
        [SerializeField] AnimationPlayer _animationPlayer;
        [SerializeField] string _attachTargetBone;
        [SerializeField] bool _followBoneRotation;

        public override void Destroy()
        {
            base.Destroy();
            Complete();
        }

        public void SetAnimationClip(AnimationClip clip)
        {
            _animationPlayer.AnimationClip = clip;
        }

        public override BaseBattleEffectView Play()
        {
            if (_animationPlayer != null)
            {
                _animationPlayer.OnDone = Complete;
                _animationPlayer.Play();
            }

            return base.Play();
        }

        public override BaseBattleEffectView BindCharacterUnit(FieldUnitView fieldUnitView)
        {
            BindCharacterImage(fieldUnitView.UnitImage);

            return base.BindCharacterUnit(fieldUnitView);
        }

        public override BaseBattleEffectView BindSpecialUnit(FieldSpecialUnitView fieldSpecialUnitView)
        {
            BindCharacterImage(fieldSpecialUnitView.UnitImage);

            return base.BindSpecialUnit(fieldSpecialUnitView);
        }

        public override BaseBattleEffectView BindCharacterImage(UnitImage unitImage)
        {
            if (!string.IsNullOrEmpty(_attachTargetBone))
            {
                SkeletonAnimationFollowerFactory.BindSkeletonAnimation(
                    gameObject,
                    unitImage.SkeletonAnimation,
                    _attachTargetBone,
                    _followBoneRotation);
            }

            return base.BindCharacterImage(unitImage);
        }

        public void OnSignalReceived(string parameter)
        {
            var signal = new BattleEffectSignal(parameter);
            InvokeSignalAction(signal);
        }

        protected override void OnPause(bool isPause)
        {
            base.OnPause(isPause);

            if (_animationPlayer != null)
            {
                _animationPlayer.Pause(isPause);
            }
        }

        /// <summary>
        /// BattleScoreEffectManagerにてObjectPoolを使用するため、
        /// スコアエフェクトでのCompleteはOnDestroyを使用しないように
        /// </summary>
        protected override void Complete()
        {
            _isCompleted = true; // PlayAsyncが呼ばれることはないため不要な設定だが念の為
            InvokeCompletedActions();
        }
    }
}

