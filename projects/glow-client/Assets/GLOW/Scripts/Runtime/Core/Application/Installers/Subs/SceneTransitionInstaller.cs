using System.Collections.Generic;
using GLOW.Core.Presentation.Transitions;
using UnityEngine;
using WonderPlanet.SceneManagement;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    [CreateAssetMenu(fileName = "GLOWSceneTransitionInstaller", menuName = "Installers/GLOW/SceneTransitionInstaller")]
    public sealed class SceneTransitionInstaller : ScriptableObjectInstaller<SceneTransitionInstaller>
    {
        [SerializeField] MaskTransition _defaultTransition;
        [SerializeField] MaskTransition[] _additionalTransitions;
        [SerializeField] InGameTransition _inGameTransition;
        [SerializeField] HomeTopTransition _homeTopTransition;

        public override void InstallBindings()
        {
            var transitions = new List<MaskTransition> { _defaultTransition };
            transitions.AddRange(_additionalTransitions);

            foreach (var transition in transitions)
            {
                Container
                    .BindFactory<MaskTransition, PlaceholderFactory<MaskTransition>>()
                    // NOTE: idを必ず指定する必要がある
                    .WithId(transition.name)
                    .FromComponentInNewPrefab(transition);
            }

            Container
                .BindFactory<InGameTransition, PlaceholderFactory<InGameTransition>>()
                // NOTE: idを必ず指定する必要がある
                .WithId(_inGameTransition.name)
                .FromComponentInNewPrefab(_inGameTransition);

            Container
                .BindFactory<HomeTopTransition, PlaceholderFactory<HomeTopTransition>>()
                // NOTE: idを必ず指定する必要がある
                .WithId(_homeTopTransition.name)
                .FromComponentInNewPrefab(_homeTopTransition);
        }
    }
}
