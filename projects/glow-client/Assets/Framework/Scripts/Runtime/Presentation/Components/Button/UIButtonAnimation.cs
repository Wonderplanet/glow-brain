using System.Collections;
using UnityEngine;
using UnityEngine.Animations;
using UnityEngine.EventSystems;
using UnityEngine.Playables;
using UnityEngine.UI;

namespace WPFramework.Presentation.Components
{
    [RequireComponent(typeof(Button))]
    [RequireComponent(typeof(Animator))]
    public class UIButtonAnimation : MonoBehaviour, IPointerClickHandler
    {
        const string ClickTrigger = "Click";
        const string OutputName = "output";

        Button _button = null;
        Animator _animator = null;

        [SerializeField] AnimationClip _appearanceAnimation = null;

        static int Click => Animator.StringToHash(ClickTrigger);

        void Awake()
        {
            _button = GetComponent<Button>();
            _animator = GetComponent<Animator>();

            // NOTE: Animator はタイムスケールを無視
            _animator.updateMode = AnimatorUpdateMode.UnscaledTime;

            if (_appearanceAnimation != null)
            {
                StartCoroutine(AppearanceOverride());
            }
        }

        IEnumerator AppearanceOverride()
        {
            var graph = PlayableGraph.Create();
            graph.SetTimeUpdateMode(DirectorUpdateMode.GameTime);
            var clipPlayable = AnimationClipPlayable.Create(graph, _appearanceAnimation);
            var output = AnimationPlayableOutput.Create(graph, OutputName, _animator);
            output.SetSourcePlayable(clipPlayable);
            graph.Play();
            clipPlayable.SetTime(0);

            yield return new WaitUntil(() => clipPlayable.GetTime() >= _appearanceAnimation.length);

            clipPlayable.SetDone(true);
            graph.Stop();
        }

        public void OnPointerClick(PointerEventData data)
        {
            if (!_button.interactable)
            {
                return;
            }

            _animator.ResetTrigger(_button.animationTriggers.normalTrigger);
            _animator.ResetTrigger(_button.animationTriggers.disabledTrigger);
            _animator.ResetTrigger(_button.animationTriggers.pressedTrigger);
            _animator.ResetTrigger(_button.animationTriggers.selectedTrigger);
            _animator.ResetTrigger(_button.animationTriggers.highlightedTrigger);
            _animator.SetTrigger(Click);
        }
    }
}
