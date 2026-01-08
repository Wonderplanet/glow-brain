using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views
{
    /// <summary>
    /// 53_バトルリザルト
    /// 　53-1_クリア
    /// </summary>
    public class VictoryAnimationView : UIView
    {
        [SerializeField] TimelineAnimation _timelineAnimation;
        [SerializeField] CanvasGroup _rootCanvasGroup;

        public Action OnCompleted
        {
            get => _timelineAnimation.OnCompleted;
            set => _timelineAnimation.OnCompleted = value;
        }

        public async UniTask PlayCloseAnimation(CancellationToken cancellationToken)
        {
            await _rootCanvasGroup.DOFade(0f, 0.2f).WithCancellation(cancellationToken);
        }
    }
}
