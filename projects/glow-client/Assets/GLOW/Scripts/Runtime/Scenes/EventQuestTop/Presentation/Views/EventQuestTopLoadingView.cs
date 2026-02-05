using System.Threading;
using Cysharp.Threading.Tasks;
using UIKit;
using UnityEngine;

namespace GLOW.Scenes.EventQuestTop.Presentation.Views
{
    public class EventQuestTopLoadingView : UIView
    {
        [Header("ロード画面")]
        [SerializeField] RectTransform _loadingViewRect;
        [SerializeField] Animator _loadingViewAnimator;

        static readonly string LoadInAnimationName = "EventQuestTopLoadingView@in";


        public void FitView()
        {
            var rect = RectTransform.rect;

            //サイズ調整処理
            var scaleFromHeight = _loadingViewRect.sizeDelta.y != 0f ? rect.height / _loadingViewRect.sizeDelta.y : 1f;
            var scaleFromWidth = _loadingViewRect.sizeDelta.x != 0f ? rect.width / _loadingViewRect.sizeDelta.x : 1f;
            var scale = Mathf.Max(scaleFromHeight, scaleFromWidth);

            _loadingViewRect.localScale = new Vector3(scale, scale, 1f);
            Debug.Log("scale: " + scale + " /height: " + scaleFromHeight + " / width: " +scaleFromWidth+  " / bgImageSize" + _loadingViewRect.sizeDelta + " / targetBgImageHeight" + rect);
        }

        public async UniTask ShowLoadingView(CancellationToken ct)
        {
            _loadingViewRect.gameObject.SetActive(true);
            _loadingViewAnimator.SetTrigger("in");

            await UniTask.WaitUntil(
                () => !_loadingViewAnimator.GetCurrentAnimatorStateInfo(0).IsName(LoadInAnimationName),
                cancellationToken: ct);
        }

        public async UniTask OutLoadingView(CancellationToken ct)
        {
            _loadingViewAnimator.SetTrigger("out");
            await UniTask.WaitUntil(() =>
                    _loadingViewAnimator.GetCurrentAnimatorStateInfo(0).normalizedTime >= 1f,
                cancellationToken: ct);

            _loadingViewRect.gameObject.SetActive(false);
        }
    }
}
