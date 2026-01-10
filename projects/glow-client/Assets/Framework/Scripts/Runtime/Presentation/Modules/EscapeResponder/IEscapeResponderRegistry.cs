namespace WPFramework.Presentation.Modules
{
    public interface IEscapeResponderRegistry
    {
        void Register(IEscapeResponder responder);
        void Unregister(IEscapeResponder responder);
    }
}
