using System.Collections;
using UnityEngine;
using UnityEngine.Playables;

namespace WPFramework.Presentation.Components
{
    public class ViewTransitionTimeLineSchema : MonoBehaviour, IViewTransitionSchema
    {
        [SerializeField] PlayableAsset _appearTimeLine = null;
        [SerializeField] PlayableAsset _disappearTimeLine = null;

        bool _isFinished;
        Coroutine _task = null;

        public bool AppearanceAnimatedEnd { get; private set; } = false;

        public void Awake()
        {
            var director = gameObject.GetComponent<PlayableDirector>();
            if (!director)
            {
                return;
            }

            director.playOnAwake = false;
            director.playableAsset = null;
        }

        void OnValidate()
        {
            var director = gameObject.GetComponent<PlayableDirector>();
            if (!director)
            {
                return;
            }

            director.playOnAwake = false;
        }

        void OnDisable()
        {
            AppearanceAnimatedEnd = false;
        }

        public Coroutine AppearanceTransition(bool isAppearing)
        {
            if (_task != null)
            {
                StopCoroutine(_task);
            }

            _task = StartCoroutine(Run(isAppearing));
            return _task;
        }

        IEnumerator Run(bool isAppearing)
        {
            var director = gameObject.GetComponent<PlayableDirector>();
            if (!director)
            {
                yield break;
            }
            director.stopped -= OnFinished;
            director.stopped += OnFinished;

            director.playableAsset = isAppearing ? _appearTimeLine : _disappearTimeLine;
            director.Play();
            director.extrapolationMode = DirectorWrapMode.None;

            _isFinished = false;
            yield return new WaitUntil(() => _isFinished);

            AppearanceAnimatedEnd = true;
        }

        void OnFinished(PlayableDirector director)
        {
            _isFinished = true;
        }
    }
}
