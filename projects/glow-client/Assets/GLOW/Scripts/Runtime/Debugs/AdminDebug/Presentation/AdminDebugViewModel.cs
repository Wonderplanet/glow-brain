using GLOW.Debugs.AdminDebug.Domain.Models;

namespace GLOW.Debugs.AdminDebug.Presentation
{
    public record AdminDebugViewModel(AdminDebugMenuCommandModel[] CommandList)
    {
        public AdminDebugMenuCommandModel[] CommandList { get; } = CommandList;
    }
}
