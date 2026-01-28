using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using WonderPlanet.SceneManagement;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public class TutorialTransitionComponent : UIObject
    {
        [SerializeField] Animator _transitionAnimation;
        
        static readonly int Appear = Animator.StringToHash("appear");
        static readonly int Disappear = Animator.StringToHash("disappear");
        
        protected override void Start()
        {
            base.Start();
            var rectTransform = this.RectTransform;
            if (rectTransform != null)
            {
                // アンカーをストレッチ（四隅を親の端に固定）
                rectTransform.anchorMin = Vector2.zero; // 左下
                rectTransform.anchorMax = Vector2.one;  // 右上

                // 上下左右のオフセットを 0 にする
                rectTransform.offsetMin = Vector2.zero; // 左下の余白
                rectTransform.offsetMax = Vector2.zero; // 右上の余白
            }
        }

        public async UniTask PlayAppear(CancellationToken cancellationToken)
        {
            var cancellationTokenSource =
                CancellationTokenSource.CreateLinkedTokenSource(this.GetCancellationTokenOnDestroy(), cancellationToken);

            try
            {
                _transitionAnimation.Update(0);
                await UniTask.DelayFrame(1, cancellationToken: cancellationTokenSource.Token);
                _transitionAnimation.SetTrigger(Appear);
                await new WaitForAnimationUntilTag(_transitionAnimation, "appeared").ToUniTask(
                    cancellationToken: cancellationTokenSource.Token);
            }
            catch (Exception e)
            {
                throw new Exception("Transition End Fail", e);
            }
            finally
            {
                cancellationTokenSource.Dispose();
                cancellationTokenSource = null;
            }
        }
        
        public async UniTask PlayDisappear(CancellationToken cancellationToken)
        {
            var cancellationTokenSource =
                CancellationTokenSource.CreateLinkedTokenSource(this.GetCancellationTokenOnDestroy(), cancellationToken);

            try
            {
                _transitionAnimation.SetTrigger(Disappear);
                await new WaitForAnimationUntilTag(_transitionAnimation, "disappeared").ToUniTask(cancellationToken: cancellationTokenSource.Token);
            }
            catch (Exception e)
            {
                throw new Exception("Transition End Fail", e);
            }
            finally
            {
                cancellationTokenSource.Dispose();
                cancellationTokenSource = null;
            }
        }
    }
}