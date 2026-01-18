using UnityEngine;
using WonderPlanet.ToastNotifier;

namespace GLOW.Modules.CommonToast.Presentation
{
    public static class CommonToastWireFrame
    {
        public static IToast ShowScreenCenterToast(string message)
        {
            var toast = Toast.MakeText(message, Toast.LengthShort + 0.25f);
            toast.SetGravity(Gravities.Center, Vector2.zero);
            toast.Show();
            return toast;
        }
        
        public static IToast ShowInvalidOperationMessage()
        {
            var toast = Toast.MakeText("その操作は現在禁止されています");
            toast.Show();
            return toast;
        }
    }
}
