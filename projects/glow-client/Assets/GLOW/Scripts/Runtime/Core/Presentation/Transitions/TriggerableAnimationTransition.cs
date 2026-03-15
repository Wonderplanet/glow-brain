using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine;
using WonderPlanet.SceneManagement;

namespace GLOW.Core.Presentation.Transitions
{
    public class TriggerableAnimationTransition : MonoBehaviour, ISceneTransition, IOverwrapTransitionTrigger
    {
        [SerializeField] float _waitSecondsAfterDestinationSceneWillAppear;
        [SerializeField] Animator _transitionAnimation;

        bool _trigger;
        static readonly int Appear = Animator.StringToHash("appear");
        static readonly int SkipMask = Animator.StringToHash("skipMask");
        static readonly int Disappear = Animator.StringToHash("disappear");

        public IOverwrapTransitionDelegate TriggerDelegate { get; set; }
        public ITransitionOperation Operation { get; set; }


        public void Abort()
        {
            Destroy(gameObject);
        }

        public async UniTask Play(CancellationToken cancellationToken)
        {
            await Run(cancellationToken);
        }

        async UniTask Run(CancellationToken cancellationToken)
        {
            var cancellationTokenSource =
                CancellationTokenSource.CreateLinkedTokenSource(this.GetCancellationTokenOnDestroy(), cancellationToken);

            try
            {
                Operation.OnTransitionBegin();

                if (!Operation.IsSkipSourceMask())
                {
                    Operation.SourceSceneWillDisappear();
                    _transitionAnimation.SetTrigger(Appear);
                    await new WaitForAnimationUntilTag(_transitionAnimation, "appeared").ToUniTask(cancellationToken: cancellationTokenSource.Token);
                }
                else
                {
                    _transitionAnimation.SetTrigger(SkipMask);
                    await new WaitForAnimationUntilTag(_transitionAnimation, "appeared").ToUniTask(cancellationToken: cancellationTokenSource.Token);
                }

                Operation.DestinationPrepare();
            }
            catch (Exception e)
            {
                throw new Exception("Transition Begin Fail", e);
            }

            await UniTask.WaitUntil(() => Operation.DestinationSceneIsReady(), cancellationToken: cancellationTokenSource.Token);

            if (TriggerDelegate == null)
            {
                var scene = SceneHelper.GetLastScene();
                var rootObjects = scene.GetRootGameObjects();
                foreach (var obj in rootObjects)
                {
                    TriggerDelegate = obj.GetComponent<IOverwrapTransitionDelegate>();
                    if (TriggerDelegate != null)
                    {
                        break;
                    }
                }
            }

            try
            {
                Operation.DestinationSceneWillAppear();
            }
            catch (Exception e)
            {
                throw new Exception("Destination Scene Init Fail", e);
            }

            if (_waitSecondsAfterDestinationSceneWillAppear != 0)
            {
                await UniTask.Delay(TimeSpan.FromSeconds(_waitSecondsAfterDestinationSceneWillAppear), cancellationToken: cancellationTokenSource.Token);
            }

            if (TriggerDelegate == null)
            {
                _trigger = true;
            }
            else
            {
                TriggerDelegate.OnDestinationSceneReady(this);
            }

            await UniTask.WaitUntil(() => _trigger, cancellationToken: cancellationTokenSource.Token);

            try
            {
                if (!Operation.IsSkipSourceMask())
                {
                    Operation.SourceSceneDidDisappear();
                }

                _transitionAnimation.SetTrigger(Disappear);
                await new WaitForAnimationUntilTag(_transitionAnimation, "disappeared").ToUniTask(cancellationToken: cancellationTokenSource.Token);

                Operation.DestinationSceneDidAppear();
                Operation.OnTransitionEnd();
            }
            catch (Exception e)
            {
                throw new Exception("Transition End Fail", e);
            }

            Destroy(gameObject);
        }

        public void TransitionComplete()
        {
            _trigger = true;
        }
    }
}
