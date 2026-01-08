using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using DG.Tweening;
using GLOW.Core.Presentation.Components;
using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Modules.Tutorial.Presentation.Views
{
    public class TutorialIntroductionMangaComponent : UIObject
    {
        const float FadeDuration = 0.3f;
        
        static readonly int BlackAnimationTrigger = Animator.StringToHash("Black");
        static readonly int OutAnimationTrigger = Animator.StringToHash("out");
        static readonly int InAnimationTrigger = Animator.StringToHash("in");
        static readonly int EndPageAnimationTrigger = Animator.StringToHash("EndPage");
        static readonly int TappedAnimationTrigger = Animator.StringToHash("onTapped");
        
        [SerializeField] CanvasGroup _canvasGroup;
        [SerializeField] List<Animator> _pageAnimators;
        [SerializeField] Animator _backgroundAnimator;
        [SerializeField] Button _tapAreaButton;
        [SerializeField] Button _skipButton;
        
        CancellationTokenSource _cancellationTokenSource;
        int _currentIndex;
        bool _onTapped;
        bool _isDisplay = false;

        public async UniTask PlayTutorialManga(CancellationToken token)
        {
            try
            {
                _tapAreaButton.onClick.AddListener(OnTappedScreen);
                _skipButton.onClick.AddListener(OnTappedSkipButton);
                _canvasGroup.alpha = 0f;

                _cancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(token);
                var cancellationToken = _cancellationTokenSource.Token;

                // フェードイン
                await WaitForFadeInView(cancellationToken);

                _currentIndex = 0;

                foreach (var animator in _pageAnimators)
                {
                    animator.gameObject.SetActive(true);

                    // 4ページ目で背景を黒くする
                    if (_currentIndex == 3)
                    {
                        _backgroundAnimator.SetTrigger(BlackAnimationTrigger);
                    }

                    while (animator.gameObject.activeSelf)
                    {

                        // 非表示まで繰り返す
                        var animatorStateInfo = animator.GetCurrentAnimatorStateInfo(0);
                        if (animatorStateInfo.tagHash == Animator.StringToHash("EndPage"))
                        {
                            _onTapped = false;

                            // タップ待ち
                            await UniTask.WaitUntil(() => _onTapped, cancellationToken: cancellationToken);
                            animator.SetTrigger(OutAnimationTrigger);
                            await UniTask.Delay(500, cancellationToken: cancellationToken);

                            // 非表示にして次のページへ
                            animator.gameObject.SetActive(false);
                            _currentIndex++;
                            _onTapped = false;
                        }
                        else
                        {
                            // ページを表示開始
                            animator.SetTrigger(InAnimationTrigger);

                            // ページを表示仕切るかタップで進行
                            await UniTask.WhenAny(
                                UniTask.WaitUntil(() => _onTapped, cancellationToken: cancellationToken),
                                UniTask.WaitUntil(() =>
                                {
                                    // 最新のアニメーション情報を取得
                                    if (animator == null) return false;
                                    var info = animator.GetCurrentAnimatorStateInfo(0);
                                    return info.tagHash == EndPageAnimationTrigger;
                                }, cancellationToken: cancellationToken));

                            // タップした場合、ページを前表示させる
                            animatorStateInfo = animator.GetCurrentAnimatorStateInfo(0);
                            if (_onTapped && animatorStateInfo.tagHash != EndPageAnimationTrigger)
                            {
                                animator.SetTrigger(TappedAnimationTrigger);
                                _onTapped = false;
                            }
                        }
                    }
                }
            }
            catch (OperationCanceledException)
            {
                Debug.Log("TutorialIntroductionMangaComponent: OperationCanceledException");
            }
            finally
            {
                TryCancelAndDispose();
            }
        }
        
        async UniTask WaitForFadeInView(CancellationToken cancellationToken)
        {
            _canvasGroup
                .DOFade(1f, FadeDuration)
                .OnComplete(() =>
                {
                    _isDisplay = true;
                })
                .SetLink(gameObject)
                .Play();
            
            // 画面が表示されるまで待機
            await UniTask.WaitUntil(() => _isDisplay, cancellationToken: cancellationToken);
        }
        
        void OnTappedScreen()
        {
            _onTapped = true;
        }

        void OnTappedSkipButton()
        {
            TryCancelAndDispose();
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            TryCancelAndDispose();
        }
        
        void TryCancelAndDispose()
        {
            if (_cancellationTokenSource != null)
            {
                if (!_cancellationTokenSource.IsCancellationRequested)
                {
                    _cancellationTokenSource.Cancel();
                }

                _cancellationTokenSource.Dispose();
                _cancellationTokenSource = null;
            }
        }
    }
}
