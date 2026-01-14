using UIKit;
using WPFramework.Modules.Observability;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class HomeBaseViewController<T> : HomeBaseViewController where T : UIView
    {
        public T ActualView => View as T;

        protected HomeBaseViewController()
        {
            // NOTE: 単独でインスタンス化することを禁止するため、protectedコンストラクタを定義
            PrefabName = typeof(T).Name;
        }
    }

    public class HomeBaseViewController : GameInteractionViewController
    {
    }
}
