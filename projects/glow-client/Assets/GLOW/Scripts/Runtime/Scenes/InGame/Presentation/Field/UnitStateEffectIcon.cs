using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class UnitStateEffectIcon : UIObject
    {
        [SerializeField] UIImage _iconSprite;
        [Header("spine情報")]
        [SerializeField] string _attachTargetBone;
        [SerializeField] bool _followBoneRotation;

        CancellationTokenSource _cancellationTokenSource;
        IReadOnlyList<StateEffectType> _stateEffectTypes = new List<StateEffectType>();
        int _displayingIndex;
        
        protected override void OnDestroy()
        {
            base.OnDestroy();
            
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;
        }

        public void BindCharacterImage(FieldUnitView fieldUnitView)
        {
            if (!string.IsNullOrEmpty(_attachTargetBone))
            {
                SkeletonAnimationFollowerFactory.BindSkeletonAnimation(gameObject,
                    fieldUnitView.SkeletonAnimation,
                    _attachTargetBone,
                    _followBoneRotation);
            }
        }
        public void UpdateStateEffects(
            IReadOnlyList<StateEffectType> stateEffectTypes,
            BattleStateEffectViewManager battleStateEffectViewManager)
        {
            var distinctStateEffectTypes = stateEffectTypes.Distinct().ToList();

            bool isStateChanged = !distinctStateEffectTypes.SequenceEqual(_stateEffectTypes);
            _stateEffectTypes = distinctStateEffectTypes;

            if (stateEffectTypes.Count == 0)
            {
                StopAnimation();
                return;
            }

            if (isStateChanged)
            {
                PlayAnimation(battleStateEffectViewManager);
            }
        }

        void PlayAnimation(BattleStateEffectViewManager battleStateEffectViewManager)
        {
            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(this.GetCancellationTokenOnDestroy());

            _iconSprite.Hidden = false;

            PlayAnimation(battleStateEffectViewManager, _cancellationTokenSource.Token).Forget();
        }

        /// <summary>
        /// アイコンを点滅させつつ、状態変化が複数の場合は順番に切り替える
        /// </summary>
        async UniTask PlayAnimation(BattleStateEffectViewManager battleStateEffectViewManager, CancellationToken cancellationToken)
        {
            _iconSprite.Image.color = new Color(1f, 1f, 1f, 0f);
            _displayingIndex = 0;

            while (true)
            {
                if (_stateEffectTypes.Count == 0) break;

                if (_displayingIndex >= _stateEffectTypes.Count)
                {
                    _displayingIndex = 0;
                }

                var stateEffectType = _stateEffectTypes[_displayingIndex];
                var viewData = battleStateEffectViewManager.GetStateEffectViewData(stateEffectType);
                if (viewData == null || viewData.Icon == null)
                {
                    _iconSprite.IsVisible = false;
                    await UniTask.Delay(800, cancellationToken: cancellationToken);
                }
                else
                {
                    _iconSprite.Image.sprite = viewData.Icon;
                    _iconSprite.Image.SetNativeSize();
                    _iconSprite.IsVisible = true;

                    await _iconSprite.Image
                        .DOFade(1f, 0.2f)
                        .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken);

                    await UniTask.Delay(800, cancellationToken: cancellationToken);

                    await _iconSprite.Image
                        .DOFade(0f, 0.2f)
                        .ToUniTask(TweenCancelBehaviour.KillAndCancelAwait, cancellationToken);
                }
                _displayingIndex++;
            }
        }

        void StopAnimation()
        {
            if (!IsAnimationPlaying()) return;

            _cancellationTokenSource?.Cancel();
            _cancellationTokenSource?.Dispose();
            _cancellationTokenSource = null;

            _iconSprite.Hidden = true;
        }

        bool IsAnimationPlaying()
        {
            return _cancellationTokenSource != null;
        }
    }
}
