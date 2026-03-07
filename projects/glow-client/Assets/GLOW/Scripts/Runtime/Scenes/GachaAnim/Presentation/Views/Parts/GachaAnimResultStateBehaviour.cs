using UnityEngine;

namespace GLOW.Scenes.GachaAnim.Presentation.Views.Parts
{
    /// <summary>
    /// GachaAnimResultComponentのAnimatorステート管理用StateMachineBehaviour
    /// 各ステートにアタッチして使用する
    /// </summary>
    public class GachaAnimResultStateBehaviour : StateMachineBehaviour
    {
        [SerializeField] ResultPhaseType _phaseType;

        public override void OnStateEnter(Animator animator, AnimatorStateInfo stateInfo, int layerIndex)
        {
            var component = animator.GetComponent<GachaAnimResultComponent>();
            if (component == null) return;

            switch (_phaseType)
            {
                case ResultPhaseType.SceneA:
                    component.OnEnterSceneA();
                    break;
                case ResultPhaseType.SceneB:
                    component.OnEnterSceneB();
                    break;
                case ResultPhaseType.SceneC:
                    component.OnEnterSceneC();
                    break;
                case ResultPhaseType.Completed:
                    component.OnCompleted();
                    break;
            }
        }
    }
}

