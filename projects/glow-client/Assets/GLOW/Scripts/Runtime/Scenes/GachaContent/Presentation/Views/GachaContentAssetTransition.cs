using System;
using UnityEngine;

namespace GLOW.Scenes.GachaContent.Presentation.Views
{
    public class GachaContentAssetTransition : MonoBehaviour
    {
        [SerializeField] GameObject _tapBlockerGameObject;
        [SerializeField] Animator _animator;

        const string StartAnimationName = "GashaTransition_in";

        Action _onTransitFill;

        public void InitializeView(Action onTransitFill)
        {
            _onTransitFill = onTransitFill;
            _tapBlockerGameObject.SetActive(false);
        }

        public void StartAnimation(float startTime)
        {
            _tapBlockerGameObject.SetActive(true);
            _animator.Play(StartAnimationName, 0, startTime);
        }

        // AnimationClipのEventから呼ばれる想定のメソッド
        public void OnTransitFill()
        {
            _onTransitFill?.Invoke();
        }

        // AnimationClipのEventから呼ばれる想定のメソッド
        public void OnEndAnimation()
        {
            _tapBlockerGameObject.SetActive(false);
        }
    }
}
