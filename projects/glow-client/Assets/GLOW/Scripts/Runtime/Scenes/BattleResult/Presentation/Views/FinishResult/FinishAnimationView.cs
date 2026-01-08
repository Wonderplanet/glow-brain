using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Views.FinishResult
{
    /// <summary>
    /// 44_降臨バトル
    /// 　44-1_降臨バトル基礎実装
    /// 　　44-1-10_降臨バトル専用バトルリザルト画面
    ///
    /// 45_強化クエスト
    /// 　42-5_1日N回強化クエスト
    /// 　　45-1-6-1_ コイン獲得クエスト専用バトルリザルト演出、バトル終了時演出など
    /// </summary>
    public class FinishAnimationView : UIView
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
