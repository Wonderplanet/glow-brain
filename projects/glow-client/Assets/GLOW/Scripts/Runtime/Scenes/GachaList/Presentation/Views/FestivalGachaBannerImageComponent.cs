using System;
using Cysharp.Threading.Tasks;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    /// <summary>
    /// フェスガシャバナー画像プレハブに付与されるコンポーネント
    /// </summary>
    public class FestivalGachaBannerImageComponent : MonoBehaviour
    {
        [SerializeField] Animator _animator;
        [SerializeField] string _inAnimationName = "in";
        [SerializeField] float _waitSeconds = 0.2f;
        
        protected void Awake()
        {
            PlayAnimationAsync().Forget();
        }

        async UniTask PlayAnimationAsync()
        {
            if(_animator == null) return;
            
            // 0.2秒待つ
            await UniTask.Delay(TimeSpan.FromSeconds(_waitSeconds), cancellationToken: this.GetCancellationTokenOnDestroy());
            _animator.Play(_inAnimationName, 0, 0);
        }
    }
}