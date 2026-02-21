using System;

namespace GLOW.Core.Application.ErrorHandle
{
    public interface ICommonExceptionViewer
    {
        void Show(string title, string body, Exception exception, Action completion);
    }
}
