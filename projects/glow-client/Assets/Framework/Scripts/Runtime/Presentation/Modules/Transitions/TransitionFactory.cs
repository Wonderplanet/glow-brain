using WonderPlanet.SceneManagement;
using Zenject;

namespace WPFramework.Presentation.Transitions
{
    public sealed class TransitionFactory : ITransitionFactory
    {
        [Inject] Context Context { get; }

        ISceneTransition ITransitionFactory.CreateTransition<T>(string name)
        {
            var factory = Context.Container.ResolveId<PlaceholderFactory<T>>(name);
            return factory.Create();
        }
    }
}
