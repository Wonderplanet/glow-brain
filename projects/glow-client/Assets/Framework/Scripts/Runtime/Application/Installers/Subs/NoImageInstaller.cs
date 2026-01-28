using UnityEngine;
using Zenject;
using WonderPlanet.ResourceManagement;

[CreateAssetMenu(fileName = "NoImageInstaller", menuName = "Installers/NoImageInstaller")]
public class NoImageInstaller : ScriptableObjectInstaller<NoImageInstaller>
{
    [SerializeField] DefaultNoImageComponentProvider noImageComponentProvider;

    public override void InstallBindings()
    {
        Container.BindInterfacesTo<DefaultNoImageComponentProvider>()
            .FromInstance(noImageComponentProvider)
            .AsCached();
    }
}
