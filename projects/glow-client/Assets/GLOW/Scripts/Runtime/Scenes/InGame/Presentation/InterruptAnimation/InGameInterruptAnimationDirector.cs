using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;

namespace GLOW.Scenes.InGame.Presentation.InterruptAnimation
{
    public class InGameInterruptAnimationDirector
    {
        List<IInGameInterruptAnimation> _animationList = new List<IInGameInterruptAnimation>();
        bool _isPlaying;
        CancellationTokenSource _cancellationTokenSource;
        IInGameInterruptAnimation _currentAnimation;
        
        public bool IsPlaying => _isPlaying;
        public bool HasAnimation => _animationList.Count > 0;

        public void Enqueue(IInGameInterruptAnimation animation)
        {
            _animationList.Add(animation);
        }

        public async UniTask PlayAnimation(CancellationToken cancellationToken)
        {
            _isPlaying = true;
            
            try
            {
                // 優先度でソート（降順: 高い優先度が先に実行される）
                var sortedAnimations = _animationList.OrderByDescending(a => a.Priority).ToList();
                _animationList.Clear();
                
                foreach (var animation in sortedAnimations)
                {
                    cancellationToken.ThrowIfCancellationRequested();
                    
                    _cancellationTokenSource?.Dispose();
                    _cancellationTokenSource = CancellationTokenSource.CreateLinkedTokenSource(cancellationToken);

                    _currentAnimation = animation;
                    await _currentAnimation.PlayAsync(_cancellationTokenSource.Token).SuppressCancellationThrow();
                    _currentAnimation = null;
                    
                    _cancellationTokenSource?.Dispose();
                    _cancellationTokenSource = null;
                }
            }
            finally
            {
                _isPlaying = false;
                _currentAnimation = null;
                _cancellationTokenSource?.Dispose();
                _cancellationTokenSource = null;
            }
        }

        public void Cancel()
        {
            _animationList.Clear();
            _cancellationTokenSource?.Cancel();
        }
        
        /// <summary>
        /// 現在再生中の演出をスキップする
        /// </summary>
        public bool SkipCurrentAnimation()
        {
            if (_currentAnimation != null && _currentAnimation.CanSkip)
            {
                _cancellationTokenSource?.Cancel();
                return true;
            }

            return false;
        }
    }
}