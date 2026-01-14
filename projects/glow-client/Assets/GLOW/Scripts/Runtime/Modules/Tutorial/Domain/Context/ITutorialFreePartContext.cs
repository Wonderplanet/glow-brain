using System;
using Cysharp.Threading.Tasks;

namespace GLOW.Modules.Tutorial.Domain.Context
{
    public interface ITutorialFreePartContext
    {
        UniTask<bool> DoIfTutorial(Func<UniTask> action);
        void InterruptTutorial();
    }
}
