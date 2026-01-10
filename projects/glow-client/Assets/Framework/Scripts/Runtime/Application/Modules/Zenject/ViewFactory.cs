using System;
using System.Collections.Generic;
using UIKit;
using WPFramework.Presentation.Modules;
using Zenject;

namespace WPFramework.Application.Modules
{
    public class ViewFactory : IViewFactory
    {
        public static string ViewContainerInstallerIdRule(Type vcType, string contextId)
        {
            return string.IsNullOrEmpty(contextId) ? vcType.Name : $"{vcType.Name}/{contextId}";
        }

        [Inject] DiContainer container;

        TV CreateInternal<TV>(IEnumerable<object> args, string contextId = null) where TV : UIViewController
        {
            var installerType = container.ResolveId<Type>(ViewContainerInstallerIdRule(typeof(TV), contextId));
            var diContainer = container.CreateSubContainer();
            var installer = (Installer)diContainer.Instantiate(installerType, args);
            installer.InstallBindings();
            diContainer.ResolveRoots();
            return diContainer.TryResolve<TV>();
        }

        public TV Create<TV>(string contextId = null) where TV : UIViewController
        {
            return CreateInternal<TV>(Array.Empty<object>(), contextId);
        }

        public TV Create<TV, TA>(TA args1, string contextId = null) where TV : UIViewController
        {
            return CreateInternal<TV>(new object[]{args1}, contextId);
        }
    }

    public static class ViewContainerInstallerExtension
    {
        public static void BindViewFactoryInfo<TV, TA>(this DiContainer container, string contextId = null) where TV : UIViewController where TA : Installer
        {
            container.BindInstance(typeof(TA))
                .WithId(ViewFactory.ViewContainerInstallerIdRule(typeof(TV), contextId))
                .AsCached();
        }
    }
}
