using UIKit;
using WPFramework.Presentation.Components;

namespace WPFramework.Presentation.Extensions
{
    public static class UIViewTransitionSchemaExtension
    {
        public static IViewTransitionSchema GetTransitionSchema(this UIView view)
        {
            var schema = view.GetComponent<IViewTransitionSchema>() ?? view.gameObject.AddComponent<ViewTransitionSchema>();
            return schema;
        }
    }
}
